<?php

namespace Rinsvent\RequestBundle\Service\Transformer;

use Rinsvent\Data2DTO\Resolver\TransformerResolverInterface;
use Rinsvent\Data2DTO\Transformer\Meta;
use Rinsvent\Data2DTO\Transformer\TransformerInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;

class ServiceResolver implements TransformerResolverInterface
{
    public const TYPE = 'service';

    public function __construct(
        private ServiceLocator $transformerLocator
    ) {}

    public function resolve(Meta $meta): TransformerInterface
    {
        $transformerClass = $meta::class . 'Transformer';
        return $this->transformerLocator->get($transformerClass);
    }
}
