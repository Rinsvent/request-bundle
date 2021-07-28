<?php

namespace Rinsvent\RequestBundle\Annotation;

#[\Attribute]
class PropertyPath
{
    public function __construct(
        public string $path
    ) {}
}