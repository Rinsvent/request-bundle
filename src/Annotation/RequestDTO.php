<?php

namespace Rinsvent\RequestBundle\Annotation;

#[\Attribute]
class RequestDTO
{
    public function __construct(
        public string $className,
        public string $jsonPath = '$',
        public ?string $attributePath = null,
    ) {}
}
