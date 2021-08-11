<?php

namespace Rinsvent\RequestBundle\Annotation;

/**
 * todo переделать на https://symfony.com/doc/current/components/property_access.html#usage
 */
#[\Attribute]
class RequestDTO
{
    public function __construct(
        public string $className,
        public string $jsonPath = '$',
        public ?string $attributePath = null,
    ) {}
}
