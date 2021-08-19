<?php

namespace Rinsvent\RequestBundle\Annotation;

#[\Attribute(\Attribute::IS_REPEATABLE)]
class RequestDTO
{
    public function __construct(
        public string $className,
        public string $jsonPath = '$',
        public ?string $attributePath = null,
    ) {}
}
