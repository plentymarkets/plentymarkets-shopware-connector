<?php

namespace SystemConnector\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ValidatorServiceCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('plenty_connector.validator_service')) {
            return;
        }

        $definition = $container->findDefinition('plenty_connector.validator_service');

        $taggedServices = $container->findTaggedServiceIds('plenty_connector.validator');

        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('addValidator', [new Reference($id)]);
        }
    }
}
