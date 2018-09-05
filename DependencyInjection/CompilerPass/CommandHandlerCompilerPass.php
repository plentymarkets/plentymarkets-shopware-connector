<?php

namespace PlentyConnector\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class CommandHandlerCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('plenty_connector.command_bus.command_handler_middleware')) {
            return;
        }

        $definition = $container->findDefinition('plenty_connector.command_bus.command_handler_middleware');

        $taggedServices = $container->findTaggedServiceIds('plenty_connector.command_handler');

        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('addHandler', [new Reference($id)]);
        }
    }
}
