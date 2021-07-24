<?php

namespace Rinsvent\RequestBundle\Annotation;

#[\Attribute]
class HeaderKey
{
    public function __construct(
        public string $key
    ) {}
}