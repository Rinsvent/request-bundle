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

// todo базовая заготовка. Требуется рефакторинг
class RequestListener
{
    public const REQUEST_DATA = 'request_data';

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
            $methodExtractor = new MethodExtractor($controller[0], $controller[1]);
        }

        /** @var RequestDTO $requestDTO */
        if ($requestDTO = $methodExtractor->fetch(RequestDTO::class)) {
            $requestDTOInstance = $this->grabRequestDTO($requestDTO->className, $request->getContent(), $request->query->all(), $request->request->all(), $request->headers->all());

            $errorCollection = $this->validate($requestDTOInstance);
            if ($errorCollection->hasErrors()) {
                $event->setResponse(new JsonResponse(['errors' => $errorCollection->format()], Response::HTTP_BAD_REQUEST));
            } else {
                $request->attributes->set(self::REQUEST_DATA, $requestDTOInstance);
            }
        }
    }

    protected function validate(object $data): ErrorCollection
    {
        $validator = Validation::createValidatorBuilder()
            ->enableAnnotationMapping(true)
            ->addDefaultDoctrineAnnotationReader()
            ->getValidator();

        $violations = $validator->validate($data);

        $errorCollection = new ErrorCollection();
        if ($violations->count()) {
            foreach ($violations as $violation) {
                $errorCollection->add(new Error($violation->getMessage(), $violation->getPropertyPath()));
            }
        }
        return $errorCollection;
    }

    protected function grabRequestDTO(string $requestClass, string $content, array $queryParameters = [], array $parameters = [], array $headers = [])
    {
        $data = [];
        try {
            $contentData = json_decode($content, true);
            $data += $contentData;
        } catch (\Throwable $e) {
        }

        $data += $queryParameters;
        $data += $parameters;
        $data += $headers;

        $data2dtoConverter = new Data2DtoConverter();
        return $data2dtoConverter->convert($data, $requestClass);
    }
}
