<?php

namespace PlentyConnector\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class EventHandlerCompilerPass
 *
 * @package PlentyConnector\Container\CompilerPass
 */
class EventHandlerCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('plentyconnector.event_bus.event_handler_middleware')) {
            return;
        }

        $definition = $container->findDefinition('plentyconnector.event_bus.event_handler_middleware');

        $taggedServices = $container->findTaggedServiceIds('plentyconnector.eventhandler');

        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('addHandler', [new Reference($id)]);
        }
    }
}
