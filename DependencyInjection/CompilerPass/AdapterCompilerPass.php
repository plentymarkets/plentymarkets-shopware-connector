<?php

namespace PlentyConnector\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class AdapterCompilerPass.
 */
class AdapterCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     *
     * @throws InvalidArgumentException
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('plenty_connector.connector')) {
            return;
        }

        $definition = $container->findDefinition('plenty_connector.connector');

        $taggedServices = $container->findTaggedServiceIds('plenty_connector.adapter');

        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('addAdapter', [new Reference($id)]);
        }
    }
}
