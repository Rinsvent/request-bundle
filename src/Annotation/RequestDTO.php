<?php

namespace Rinsvent\RequestBundle\Annotation;

#[\Attribute(\Attribute::IS_REPEATABLE|\Attribute::TARGET_ALL)]
class RequestDTO
{
    public function __construct(
        public string $className,
        public string $jsonPath = '$',
        public ?string $attributePath = null,
    ) {
    }
}
