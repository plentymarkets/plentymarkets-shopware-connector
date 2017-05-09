<?php

namespace PlentyConnector;

use Doctrine\DBAL\Exception\InvalidArgumentException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use PlentyConnector\Connector\ConfigService\Model\Config;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\IdentityService\Model\Identity;
use PlentyConnector\Connector\TransferObject\Category\Category;
use PlentyConnector\Connector\TransferObject\PaymentStatus\PaymentStatus;
use PlentyConnector\DependencyInjection\CompilerPass\CleanupDefinitionCompilerPass;
use PlentyConnector\DependencyInjection\CompilerPass\CommandGeneratorCompilerPass;
use PlentyConnector\DependencyInjection\CompilerPass\CommandHandlerCompilerPass;
use PlentyConnector\DependencyInjection\CompilerPass\ConnectorDefinitionCompilerPass;
use PlentyConnector\DependencyInjection\CompilerPass\EventHandlerCompilerPass;
use PlentyConnector\DependencyInjection\CompilerPass\MappingDefinitionCompilerPass;
use PlentyConnector\DependencyInjection\CompilerPass\QueryGeneratorCompilerPass;
use PlentyConnector\DependencyInjection\CompilerPass\QueryHandlerCompilerPass;
use PlentyConnector\DependencyInjection\CompilerPass\ValidatorServiceCompilerPass;
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

    /**
     * List of all cronjobs
     */
    const CRONJOB_SYNCHRONIZE = 'Synchronize';
    const CRONJOB_SYNCHRONIZE_ORDERS = 'SynchronizeOrders';
    const CRONJOB_CLEANUP = 'Cleanup';

    const CRONJOBS = [
        self::CRONJOB_SYNCHRONIZE => 300,
        self::CRONJOB_SYNCHRONIZE_ORDERS => 300,
        self::CRONJOB_CLEANUP => 86400,
    ];

    /**
     * @param ContainerBuilder $container
     * @param array $plugins
     *
     * @return bool
     */
    private function pluginExists(ContainerBuilder $container, array $plugins)
    {
        $folders = $container->getParameter('shopware.plugin_directories');

        foreach ($plugins as $pluginName) {
            foreach ($folders as $folder) {
                if (file_exists($folder . 'Backend/' . $pluginName)) {
                    return true;
                }

                if (file_exists($folder . 'Core/' . $pluginName)) {
                    return true;
                }

                if (file_exists($folder . 'Frontend/' . $pluginName)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param ContainerBuilder $container
     *
     * @throws Exception
     */
    public function build(ContainerBuilder $container)
    {
        $container->setParameter('plenty_connector.plugin_dir', $this->getPath());

        $this->loadFile($container, __DIR__ . '/DependencyInjection/services.xml');

        // Adapter
        $this->loadFile($container, __DIR__ . '/Adapter/ShopwareAdapter/DependencyInjection/services.xml');
        $this->loadFile($container, __DIR__ . '/Adapter/PlentymarketsAdapter/DependencyInjection/services.xml');

        // Payments
        $this->loadFile($container, __DIR__ . '/Components/Sepa/DependencyInjection/services.xml');

        if ($this->pluginExists($container, ['SwagPaymentPaypal', 'SwagPaymentPayPalInstallments', 'SwagPaymentPaypalPlus'])) {
            $this->loadFile($container, __DIR__ . '/Components/PayPal/DependencyInjection/services.xml');
        }

        if ($this->pluginExists($container, ['SwagBundle'])) {
            $this->loadFile($container, __DIR__ . '/Components/Bundle/DependencyInjection/services.xml');
        }

        $container->addCompilerPass(new CleanupDefinitionCompilerPass());
        $container->addCompilerPass(new CommandGeneratorCompilerPass());
        $container->addCompilerPass(new CommandHandlerCompilerPass());
        $container->addCompilerPass(new ConnectorDefinitionCompilerPass());
        $container->addCompilerPass(new EventHandlerCompilerPass());
        $container->addCompilerPass(new MappingDefinitionCompilerPass());
        $container->addCompilerPass(new QueryGeneratorCompilerPass());
        $container->addCompilerPass(new QueryHandlerCompilerPass());
        $container->addCompilerPass(new ValidatorServiceCompilerPass());

        parent::build($container);
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

        if ($this->updateNeeded($context, '2.0.0-rc2')) {
            $this->clearCategoryIdentities();
            $this->clearPaymentStatusIdentities();
            $this->clearLastChangedConfigEntries();
        }

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

    /**
     * @param UpdateContext $context
     * @param $targetVersion
     *
     * @return mixed
     */
    private function updateNeeded(UpdateContext $context, $targetVersion)
    {
        return version_compare($context->getCurrentVersion(), $targetVersion, '<');
    }

    /**
     * remove category identities as we changed the mapping format.
     */
    private function clearCategoryIdentities()
    {
        /**
         * @var IdentityServiceInterface $identityService
         */
        $identityService = $this->container->get('plenty_connector.identity_service');

        foreach ($identityService->findBy(['objectType' => Category::TYPE]) as $identity) {
            $identityService->remove($identity);
        }
    }

    /**
     * remove category identities as we changed what informations are mapped
     */
    private function clearPaymentStatusIdentities()
    {
        /**
         * @var IdentityServiceInterface $identityService
         */
        $identityService = $this->container->get('plenty_connector.identity_service');

        foreach ($identityService->findBy(['objectType' => PaymentStatus::TYPE]) as $identity) {
            $identityService->remove($identity);
        }
    }

    /**
     * reimport everything as we changed the config format
     */
    private function clearLastChangedConfigEntries()
    {
        /**
         * @var EntityManagerInterface $models
         */
        $entityManager = $this->container->get('models');
        $repository = $entityManager->getRepository(Config::class);

        /**
         * @var Config $element
         */
        foreach ($repository->findAll() as $element) {
            if (false !== stripos($element->getName(), 'rest_')) {
                continue;
            }

            $entityManager->remove($element);
        }

        $entityManager->flush();
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
}
