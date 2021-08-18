<?php

namespace Rinsvent\RequestBundle\EventListener;

use Rinsvent\Data2DTO\Data2DtoConverter;
use Rinsvent\RequestBundle\Annotation\RequestDTO;
use Rinsvent\RequestBundle\DTO\Error;
use Rinsvent\RequestBundle\DTO\ErrorCollection;
use Rinsvent\AttributeExtractor\MethodExtractor;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Validator\Validation;

class RequestListener
{
    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        $controller = $request->get('_controller');
        if (is_string($controller)) {
            $controller = explode('::', $controller);
        }
        if (is_callable($controller)) {
            if (is_object($controller[0])) {
                $controller[0] = get_class($controller[0]);
            }
        }
        if (!is_array($controller) || !count($controller) === 2) {
            return;
        }
        $methodExtractor = new MethodExtractor($controller[0], $controller[1]);

        $tags = [
            'default',
            'request',
            'request_body',
            'request_headers',
            'request_server',
            'route_' . $request->get('_route')
        ];

        /** @var RequestDTO $requestDTO */
        while ($requestDTO = $methodExtractor->fetch(RequestDTO::class)) {
            $requestDTOInstance = $this->grabRequestDTO(
                $requestDTO,
                $tags,
                $request->getContent(),
                $request->query->all(),
                $request->request->all()
            );

            $errorCollection = $this->validate($requestDTOInstance, $tags);
            if ($errorCollection->hasErrors()) {
                $event->setResponse(new JsonResponse(['errors' => $errorCollection->format()], Response::HTTP_BAD_REQUEST));
            } else {
                $reflectionObject = new \ReflectionObject($requestDTOInstance);
                $requestDTOName = $reflectionObject->getShortName();
                $attributePath = lcfirst($requestDTOName);

                if ($requestDTO->attributePath) {
                    $attributePath = $requestDTO->attributePath;
                }

                if ($request->attributes->has($attributePath)) {
                    throw new \Exception('Same request data has already exists!');
                }
                $request->attributes->set($attributePath, $requestDTOInstance);
            }
        }
    }

    protected function validate(object $data, array $tags): ErrorCollection
    {
        $tags = array_merge($tags, ['Default']);
        $validator = Validation::createValidatorBuilder()
            ->enableAnnotationMapping(true)
            ->addDefaultDoctrineAnnotationReader()
            ->getValidator();

        $violations = $validator->validate($data, null, $tags);

        $errorCollection = new ErrorCollection();
        if ($violations->count()) {
            foreach ($violations as $violation) {
                $errorCollection->add(new Error($violation->getMessage(), $violation->getPropertyPath()));
            }
        }
        return $errorCollection;
    }

    protected function grabRequestDTO(
        RequestDTO $requestDTO,
        array      $tags,
        string     $content,
        array      $queryParameters = [],
        array      $parameters = []
    ) {
        $data = [];
        try {
            $contentData = json_decode($content, true);
            $data += $contentData;
        } catch (\Throwable $e) {
        }

        $data += $queryParameters;
        $data += $parameters;
        $data = $this->grabDataByJsonPath($data, $requestDTO->jsonPath);

        $data2dtoConverter = new Data2DtoConverter();
        $result = $data2dtoConverter->convert(
            $data,
            new $requestDTO->className,
            $tags
        );

        return $result;
    }

    private function grabDataByJsonPath(array $data, string $jsonPath): array
    {
        if ($jsonPath !== '$') {
            $jsonPath = trim($jsonPath, '$');
            $jsonPath = trim($jsonPath, '.');
            $jsonPath = explode('.', $jsonPath);
            if (is_array($jsonPath)) {
                foreach ($jsonPath as $item) {
                    $data = $data[$item] ?? null;
                    if ($data === null) {
                        return [];
                    }
                }
            }
        }
        return $data;
    }
}
