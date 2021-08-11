<?php

namespace Rinsvent\RequestBundle\Service\Transformer;

use Rinsvent\Data2DTO\Transformer\Meta;

#[\Attribute]
class Entity extends Meta
{
    public const TYPE = 'service';

    public function __construct(
        public string $class
    ) {}
}
