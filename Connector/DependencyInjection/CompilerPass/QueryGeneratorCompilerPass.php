<?php

namespace SystemConnector\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class QueryGeneratorCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('plenty_connector.query_factory')) {
            return;
        }

        $definition = $container->findDefinition('plenty_connector.query_factory');

        $taggedServices = $container->findTaggedServiceIds('plenty_connector.query_generator');

        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('addGenerator', [new Reference($id)]);
        }
    }
}
