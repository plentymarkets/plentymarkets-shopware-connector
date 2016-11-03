<?php

namespace PlentyConnector;

use Exception;
use PlentyConnector\DependencyInjection\CompilerPass\AdapterCompilerPass;
use PlentyConnector\DependencyInjection\CompilerPass\CommandHandlerCompilerPass;
use PlentyConnector\DependencyInjection\CompilerPass\ConnectorDefinitionCompilerPass;
use PlentyConnector\DependencyInjection\CompilerPass\EventHandlerCompilerPass;
use PlentyConnector\DependencyInjection\CompilerPass\MappingDefinitionCompilerPass;
use PlentyConnector\DependencyInjection\CompilerPass\ParameterCompilerPass;
use PlentyConnector\DependencyInjection\CompilerPass\QueryHandlerCompilerPass;
use PlentyConnector\Installer\DatabaseInstaller;
use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UpdateContext;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

require __DIR__.'/autoload.php';

/**
 * Class PlentyConnector.
 */
class PlentyConnector extends Plugin
{
    /**
     * @param ContainerBuilder $container
     *
     * @throws Exception
     */
    public function build(ContainerBuilder $container)
    {
        $container->setParameter('plentyconnector.plugin_dir', $this->getPath());

        $this->loadFile($container, __DIR__.'/Adapter/ShopwareAdapter/DependencyInjection/services.xml');
        $this->loadFile($container, __DIR__.'/Adapter/PlentymarketsAdapter/DependencyInjection/services.xml');
        $this->loadFile($container, __DIR__.'/DependencyInjection/services.xml');

        $container->addCompilerPass(new AdapterCompilerPass());
        $container->addCompilerPass(new CommandHandlerCompilerPass());
        $container->addCompilerPass(new ConnectorDefinitionCompilerPass());
        $container->addCompilerPass(new EventHandlerCompilerPass());
        $container->addCompilerPass(new MappingDefinitionCompilerPass());
        $container->addCompilerPass(new QueryHandlerCompilerPass());

        parent::build($container);
    }

    /**
     * @param ContainerBuilder $container
     * @param $filename
     *
     * @throws Exception
     */
    private function loadFile(ContainerBuilder $container, $filename)
    {
        if (!is_file($filename)) {
            return;
        }

        $loader = new XmlFileLoader(
            $container,
            new FileLocator()
        );

        $loader->load($filename);
    }

    /**
     * @param InstallContext $context
     *
     * @throws ServiceCircularReferenceException
     * @throws ServiceNotFoundException
     */
    public function install(InstallContext $context)
    {
        /**
         * @var DatabaseInstaller
         */
        $databaseInstaller = new DatabaseInstaller($this->container->get('models'));
        $databaseInstaller->install($context);

        parent::install($context);
    }

    /**
     * @param UpdateContext $context
     *
     * @throws ServiceCircularReferenceException
     * @throws ServiceNotFoundException
     */
    public function update(UpdateContext $context)
    {
        /**
         * @var DatabaseInstaller
         */
        $databaseInstaller = new DatabaseInstaller($this->container->get('models'));
        $databaseInstaller->update($context);

        parent::update($context);
    }
}
