<?php

namespace Rinsvent\RequestBundle\DependencyInjection;

use Rinsvent\RequestBundle\Service\Transformer\AbstractTransformer;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class RequestExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');

        // $container->findTaggedServiceIds(AbstractTransformer::class);
    }
}
