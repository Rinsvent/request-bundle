<?php

namespace Rinsvent\RequestBundle\EventListener;

use JMS\Serializer\SerializerBuilder;
use ReflectionObject;
use Rinsvent\AttributeExtractor\PropertyExtractor;
use Rinsvent\RequestBundle\Annotation\RequestDTO;
use Rinsvent\RequestBundle\Annotation\HeaderKey;
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
            $propertyExtractor = new PropertyExtractor($object::class, $property->getName());
            /** @var HeaderKey $headerKey */
            if ($headerKey = $propertyExtractor->fetch(HeaderKey::class)) {
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
