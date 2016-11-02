<?php

namespace PlentyConnector\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class MappingDefinitionCompilerPass
 * @package PlentyConnector\DependencyInjection\CompilerPass
 */
class MappingDefinitionCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     *
     * @throws InvalidArgumentException
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('plentyconnector.connector')) {
            return;
        }

        $definition = $container->findDefinition('plentyconnector.connector');

        $taggedServices = $container->findTaggedServiceIds('plentyconnector.mapping_definition');

        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('addDefinition', [new Reference($id)]);
        }
    }
}
