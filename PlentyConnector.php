<?php

namespace PlentyConnector;

use Doctrine\DBAL\Exception\InvalidArgumentException;
use Exception;
use PlentyConnector\Connector\ConfigService\Model\Config;
use PlentyConnector\Connector\IdentityService\Model\Identity;
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
use PlentyConnector\Installer\PermissionInstaller;
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
    const PERMISSION_READ = 'read';
    const PERMISSION_WRITE = 'write';

    /**
     * List of all permissions
     */
    const PERMISSIONS = [
        self::PERMISSION_READ,
        self::PERMISSION_WRITE,
    ];

    /**
     * List of all models
     */
    const MODELS = [
        Config::class,
        Identity::class,
    ];

    const CRONJOB_SYNCHRONIZE = 'Synchronize';
    const CRONJOB_CLEANUP = 'Cleanup';

    /**
     * List of all cronjobs
     */
    const CRONJOBS = [
        self::CRONJOB_SYNCHRONIZE => 300,
        self::CRONJOB_CLEANUP => 86400,
    ];

    /**
     * @param ContainerBuilder $container
     *
     * @throws Exception
     */
    public function build(ContainerBuilder $container)
    {
        $container->setParameter('plenty_connector.plugin_dir', $this->getPath());

        $this->loadFile($container, __DIR__ . '/Adapter/ShopwareAdapter/DependencyInjection/services.xml');
        $this->loadFile($container, __DIR__ . '/Adapter/PlentymarketsAdapter/DependencyInjection/services.xml');
        $this->loadFile($container, __DIR__ . '/DependencyInjection/services.xml');

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
     * @throws Exception
     */
    public function install(InstallContext $context)
    {
        // Models
        $databaseInstaller = new DatabaseInstaller(
            $this->container->get('models'),
            self::MODELS
        );
        $databaseInstaller->install($context);

        // Cronjobs
        $cronjobInstaller = new CronjobInstaller(
            $this->container->get('dbal_connection'),
            self::CRONJOBS
        );
        $cronjobInstaller->install($context);

        // Permissions
        $permissionInstaller = new PermissionInstaller(
            $this->container->get('models'),
            $this->container->get('acl'),
            self::PERMISSIONS
        );
        $permissionInstaller->install($context);

        parent::install($context);
    }

    /**
     * @param UpdateContext $context
     *
     * @throws ServiceCircularReferenceException
     * @throws ServiceNotFoundException
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function update(UpdateContext $context)
    {
        // Models
        $databaseInstaller = new DatabaseInstaller(
            $this->container->get('models'),
            self::MODELS
        );
        $databaseInstaller->update($context);

        // Cronjobs
        $cronjobInstaller = new CronjobInstaller(
            $this->container->get('dbal_connection'),
            self::CRONJOBS
        );
        $cronjobInstaller->update($context);

        // Permissions
        $permissionInstaller = new PermissionInstaller(
            $this->container->get('models'),
            $this->container->get('acl'),
            self::PERMISSIONS
        );
        $permissionInstaller->update($context);

        parent::update($context);
    }

    /**
     * @param UninstallContext $context
     *
     * @throws ServiceCircularReferenceException
     * @throws ServiceNotFoundException
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function uninstall(UninstallContext $context)
    {
        // Models
        $databaseInstaller = new DatabaseInstaller(
            $this->container->get('models'),
            self::MODELS
        );
        $databaseInstaller->uninstall($context);

        // Cronjobs
        $cronjobInstaller = new CronjobInstaller(
            $this->container->get('dbal_connection'),
            self::CRONJOBS
        );
        $cronjobInstaller->uninstall($context);

        $permissionInstaller = new PermissionInstaller(
            $this->container->get('models'),
            $this->container->get('acl'),
            self::PERMISSIONS
        );
        $permissionInstaller->uninstall($context);

        parent::uninstall($context);
    }
}
