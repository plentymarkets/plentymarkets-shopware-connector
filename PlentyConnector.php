<?php

namespace PlentyConnector;

use Doctrine\DBAL\Exception\InvalidArgumentException;
use Exception;
use PlentyConnector\DependencyInjection\CompilerPass\AdapterCompilerPass;
use PlentyConnector\DependencyInjection\CompilerPass\CleanupDefinitionCompilerPass;
use PlentyConnector\DependencyInjection\CompilerPass\CommandGeneratorCompilerPass;
use PlentyConnector\DependencyInjection\CompilerPass\CommandHandlerCompilerPass;
use PlentyConnector\DependencyInjection\CompilerPass\ConnectorDefinitionCompilerPass;
use PlentyConnector\DependencyInjection\CompilerPass\EventHandlerCompilerPass;
use PlentyConnector\DependencyInjection\CompilerPass\MappingDefinitionCompilerPass;
use PlentyConnector\DependencyInjection\CompilerPass\ParameterCompilerPass;
use PlentyConnector\DependencyInjection\CompilerPass\QueryGeneratorCompilerPass;
use PlentyConnector\DependencyInjection\CompilerPass\QueryGeneratorCompilerPassimplements;
use PlentyConnector\DependencyInjection\CompilerPass\QueryHandlerCompilerPass;
use PlentyConnector\Installer\CronjobInstaller;
use PlentyConnector\Installer\DatabaseInstaller;
use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use Shopware\Components\Plugin\Context\UpdateContext;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

require __DIR__ . '/autoload.php';

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

        $this->loadFile($container, __DIR__ . '/Adapter/ShopwareAdapter/DependencyInjection/services.xml');
        $this->loadFile($container, __DIR__ . '/Adapter/PlentymarketsAdapter/DependencyInjection/services.xml');
        $this->loadFile($container, __DIR__ . '/DependencyInjection/services.xml');

        $container->addCompilerPass(new AdapterCompilerPass());
        $container->addCompilerPass(new CleanupDefinitionCompilerPass());
        $container->addCompilerPass(new CommandGeneratorCompilerPass());
        $container->addCompilerPass(new CommandHandlerCompilerPass());
        $container->addCompilerPass(new ConnectorDefinitionCompilerPass());
        $container->addCompilerPass(new EventHandlerCompilerPass());
        $container->addCompilerPass(new MappingDefinitionCompilerPass());
        $container->addCompilerPass(new QueryGeneratorCompilerPass());
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
     * @throws InvalidArgumentException
     */
    public function install(InstallContext $context)
    {
        $databaseInstaller = new DatabaseInstaller($this->container->get('models'));
        $databaseInstaller->install($context);

        $databaseInstaller = new CronjobInstaller($this->container->get('dbal_connection'));
        $databaseInstaller->install($context);

        parent::install($context);
    }

    /**
     * @param UpdateContext $context
     *
     * @throws ServiceCircularReferenceException
     * @throws ServiceNotFoundException
     * @throws InvalidArgumentException
     */
    public function update(UpdateContext $context)
    {
        $databaseInstaller = new DatabaseInstaller($this->container->get('models'));
        $databaseInstaller->update($context);

        $databaseInstaller = new CronjobInstaller($this->container->get('dbal_connection'));
        $databaseInstaller->update($context);

        parent::update($context);
    }

    /**
     * @param UninstallContext $context
     *
     * @throws ServiceCircularReferenceException
     * @throws ServiceNotFoundException
     * @throws InvalidArgumentException
     */
    public function uninstall(UninstallContext $context)
    {
        $databaseInstaller = new DatabaseInstaller($this->container->get('models'));
        $databaseInstaller->uninstall($context);

        $databaseInstaller = new CronjobInstaller($this->container->get('dbal_connection'));
        $databaseInstaller->uninstall($context);

        parent::uninstall($context);
    }
}
