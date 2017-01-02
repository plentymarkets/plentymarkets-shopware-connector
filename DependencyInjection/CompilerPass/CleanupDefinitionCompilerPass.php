<?php

namespace PlentyConnector\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class DefinitionCompilerPass.
 */
class CleanupDefinitionCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     *
     * @throws InvalidArgumentException
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('plentyconnector.cleanup_service')) {
            return;
        }

        $definition = $container->findDefinition('plentyconnector.cleanup_service');

        $taggedServices = $container->findTaggedServiceIds('plentyconnector.connector_definition');

        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('addDefinition', [new Reference($id)]);
        }
    }
}
