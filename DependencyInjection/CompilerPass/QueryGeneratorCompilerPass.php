<?php

namespace PlentyConnector\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class QueryGeneratorCompilerPassimplements
 */
class QueryGeneratorCompilerPassimplements implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     *
     * @throws InvalidArgumentException
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('plentyconnector.query_factory')) {
            return;
        }

        $definition = $container->findDefinition('plentyconnector.query_factory');

        $taggedServices = $container->findTaggedServiceIds('plentyconnector.query_generator');

        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('addGenerator', [new Reference($id)]);
        }
    }
}
