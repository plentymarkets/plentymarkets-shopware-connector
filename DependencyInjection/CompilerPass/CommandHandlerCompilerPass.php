<?php

namespace PlentyConnector\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class CommandHandlerCompilerPass
 *
 * @package PlentyConnector\Container\CompilerPass
 */
class CommandHandlerCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('plentyconnector.command_bus.command_handler_middleware')) {
            return;
        }

        $definition = $container->findDefinition('plentyconnector.command_bus.command_handler_middleware');

        $taggedServices = $container->findTaggedServiceIds('plentyconnector.commandhandler');

        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('addHandler', [new Reference($id)]);
        }
    }
}
