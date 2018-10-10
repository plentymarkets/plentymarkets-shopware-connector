<?php

namespace SystemConnector\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class CleanupDefinitionCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('plenty_connector.definition_provider')) {
            return;
        }

        $definition = $container->findDefinition('plenty_connector.definition_provider');

        $taggedServices = $container->findTaggedServiceIds('plenty_connector.cleanup_definition');

        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('addCleanupDefinition', [new Reference($id)]);
        }
    }
}
