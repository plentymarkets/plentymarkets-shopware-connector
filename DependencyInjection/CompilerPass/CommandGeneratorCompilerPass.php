<?php

namespace PlentyConnector\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class CommandGeneratorCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('plenty_connector.command_factory')) {
            return;
        }

        $definition = $container->findDefinition('plenty_connector.command_factory');

        $taggedServices = $container->findTaggedServiceIds('plenty_connector.command_generator');

        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('addGenerator', [new Reference($id)]);
        }
    }
}
