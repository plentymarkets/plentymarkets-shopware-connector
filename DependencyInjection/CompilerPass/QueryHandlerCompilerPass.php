<?php

namespace PlentyConnector\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class QueryHandlerCompilerPass.
 */
class QueryHandlerCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('plentyconnector.query_bus.query_handler_middleware')) {
            return;
        }

        $definition = $container->findDefinition('plentyconnector.query_bus.query_handler_middleware');

        $taggedServices = $container->findTaggedServiceIds('plentyconnector.queryhandler');

        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('addHandler', [new Reference($id)]);
        }
    }
}
