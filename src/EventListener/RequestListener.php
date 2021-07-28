<?php

namespace Rinsvent\RequestBundle\EventListener;

use JMS\Serializer\SerializerBuilder;
use ReflectionMethod;
use ReflectionObject;
use Rinsvent\RequestBundle\Annotation\RequestDTO;
use Rinsvent\RequestBundle\Annotation\HeaderKey;
use Rinsvent\RequestBundle\DTO\Error;
use Rinsvent\RequestBundle\DTO\ErrorCollection;
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
            $method = new ReflectionMethod($controller[0], $controller[1]);
        }
        if (is_callable($controller)) {
            $method = new ReflectionMethod($controller[0], $controller[1]);
        }
        if (!isset($method)) {
            return;
        }

        $attributes = $method->getAttributes(RequestDTO::class);
        $attribute = $attributes[0] ?? null;
        if ($attribute) {
            /** @var RequestDTO $requestDTO */
            $requestDTO = $attribute->newInstance();
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

    /**
     * todo переделать на перебор филлеров
     * Сделать регистрацию филлеров
     * и выексти филеры заполнения entity и documents в отдельных бандлах
     */
    protected function grabRequestDTO(string $requestClass, string $content, array $queryParameters = [], array $parameters = [], array $headers = [])
    {
        $serializer = SerializerBuilder::create()->build();
        if ($content) {
            $object = $serializer->deserialize($content, $requestClass, 'json');
        } else {
            $object = new $requestClass;
        }
        $this->fillFromData($object, $queryParameters);
        $this->fillFromData($object, $parameters);
        $this->fillFromData($object, $headers);
        return $object;
    }

    protected function fillFromData(object $object, array $data): object
    {
        $reflectionObject = new ReflectionObject($object);
        $properties = $reflectionObject->getProperties();
        foreach ($properties as $property) {
            $attributes = $property->getAttributes(HeaderKey::class);
            $attribute = $attributes[0] ?? null;
            if ($attribute) {
                /** @var HeaderKey $headerKey */
                $headerKey = $attribute->newInstance();
                $value = $data[strtolower($headerKey->key)][0] ?? null;
            } else {
                $value = $data[$property->getName()] ?? null;
            }
            if ($value) {
                $property->setValue($object, $value);
            }
        }

        return $object;
    }
}
