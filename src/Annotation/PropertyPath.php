<?php

namespace Rinsvent\RequestBundle\Annotation;

#[\Attribute(\Attribute::IS_REPEATABLE|\Attribute::TARGET_ALL)]
class PropertyPath
{
    public function __construct(
        public string $path
    ) {}
}
