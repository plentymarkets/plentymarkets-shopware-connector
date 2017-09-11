<?php

namespace PlentyConnector;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PlentyConnector\Connector\ConfigService\Model\Config;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\IdentityService\Model\Identity;
use PlentyConnector\Connector\TransferObject\Category\Category;
use PlentyConnector\Connector\TransferObject\PaymentStatus\PaymentStatus;
use PlentyConnector\DependencyInjection\CompilerPass\CleanupDefinitionCompilerPass;
use PlentyConnector\DependencyInjection\CompilerPass\CommandGeneratorCompilerPass;
use PlentyConnector\DependencyInjection\CompilerPass\CommandHandlerCompilerPass;
use PlentyConnector\DependencyInjection\CompilerPass\ConnectorDefinitionCompilerPass;
use PlentyConnector\DependencyInjection\CompilerPass\MappingDefinitionCompilerPass;
use PlentyConnector\DependencyInjection\CompilerPass\QueryGeneratorCompilerPass;
use PlentyConnector\DependencyInjection\CompilerPass\QueryHandlerCompilerPass;
use PlentyConnector\DependencyInjection\CompilerPass\ValidatorServiceCompilerPass;
use PlentyConnector\Installer\CronjobInstaller;
use PlentyConnector\Installer\DatabaseInstaller;
use PlentyConnector\Installer\PermissionInstaller;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\ActivateContext;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use Shopware\Components\Plugin\Context\UpdateContext;
use Shopware_Components_Acl;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
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
     * List of all cronjobs
     */
    const CRONJOB_SYNCHRONIZE = 'Synchronize';
    const CRONJOB_SYNCHRONIZE_ORDERS = 'SynchronizeOrders';
    const CRONJOB_CLEANUP = 'Cleanup';

    /**
     * List of all permissions
     */
    public static $permissions = [
        self::PERMISSION_READ,
        self::PERMISSION_WRITE,
    ];

    /**
     * List of all models
     */
    public static $models = [
        Config::class,
        Identity::class,
    ];

    /**
     * List of all cronjobs
     */
    public static $cronjobs = [
        self::CRONJOB_SYNCHRONIZE => 300,
        self::CRONJOB_SYNCHRONIZE_ORDERS => 300,
        self::CRONJOB_CLEANUP => 86400,
    ];

    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        $container->setParameter('plenty_connector.plugin_dir', $this->getPath());

        $this->loadFile($container, __DIR__ . '/DependencyInjection/services.xml');

        $this->loadFile($container, __DIR__ . '/Adapter/ShopwareAdapter/DependencyInjection/services.xml');
        $this->loadFile($container, __DIR__ . '/Adapter/PlentymarketsAdapter/DependencyInjection/services.xml');

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
        $container->addCompilerPass(new MappingDefinitionCompilerPass());
        $container->addCompilerPass(new QueryGeneratorCompilerPass());
        $container->addCompilerPass(new QueryHandlerCompilerPass());
        $container->addCompilerPass(new ValidatorServiceCompilerPass());

        parent::build($container);
    }

    /**
     * @param InstallContext $context
     */
    public function install(InstallContext $context)
    {
        $this->clearOldDatabaseTables();

        /**
         * @var ModelManager $models
         */
        $models = $this->container->get('models');

        /**
         * @var Shopware_Components_Acl $acl
         */
        $acl = $this->container->get('acl');

        // Models
        $databaseInstaller = new DatabaseInstaller(
            $models,
            self::$models
        );
        $databaseInstaller->install($context);

        // Cronjobs
        $cronjobInstaller = new CronjobInstaller(
            $models->getConnection(),
            self::$cronjobs
        );
        $cronjobInstaller->install($context);

        // Permissions
        $permissionInstaller = new PermissionInstaller(
            $models,
            $acl,
            self::$permissions
        );
        $permissionInstaller->install($context);

        parent::install($context);
    }

    /**
     * @param UpdateContext $context
     */
    public function update(UpdateContext $context)
    {
        if ($this->updateNeeded($context, '2.0.0')) {
            $this->clearOldDatabaseTables();
        }

        /**
         * @var ModelManager $models
         */
        $models = $this->container->get('models');

        /**
         * @var Shopware_Components_Acl $acl
         */
        $acl = $this->container->get('acl');

        // Models
        $databaseInstaller = new DatabaseInstaller(
            $models,
            self::$models
        );
        $databaseInstaller->update($context);

        // Cronjobs
        $cronjobInstaller = new CronjobInstaller(
            $models->getConnection(),
            self::$cronjobs
        );
        $cronjobInstaller->update($context);

        // Permissions
        $permissionInstaller = new PermissionInstaller(
            $models,
            $acl,
            self::$permissions
        );
        $permissionInstaller->update($context);

        if ($this->updateNeeded($context, '2.0.0-rc2') && $this->updatePossible($context, '2.0.0')) {
            $this->clearCategoryIdentities();
            $this->clearPaymentStatusIdentities();
            $this->clearLastChangedConfigEntries();
        }

        parent::update($context);
    }

    /**
     * @param UninstallContext $context
     */
    public function uninstall(UninstallContext $context)
    {
        /**
         * @var ModelManager $models
         */
        $models = $this->container->get('models');

        /**
         * @var Shopware_Components_Acl $acl
         */
        $acl = $this->container->get('acl');

        // Models
        $databaseInstaller = new DatabaseInstaller(
            $models,
            self::$models
        );
        $databaseInstaller->uninstall($context);

        // Cronjobs
        $cronjobInstaller = new CronjobInstaller(
            $models->getConnection(),
            self::$cronjobs
        );
        $cronjobInstaller->uninstall($context);

        // Permissions
        $permissionInstaller = new PermissionInstaller(
            $models,
            $acl,
            self::$permissions
        );
        $permissionInstaller->uninstall($context);

        parent::uninstall($context);
    }

    /**
     * @param ActivateContext $context
     *
     * @throws \RuntimeException
     */
    public function activate(ActivateContext $context)
    {
        $context->scheduleClearCache(InstallContext::CACHE_LIST_ALL);

        parent::activate($context);
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $plugins
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
     * @param UpdateContext $context
     * @param $targetVersion
     *
     * @return mixed
     */
    private function updatePossible(UpdateContext $context, $targetVersion)
    {
        return version_compare($context->getCurrentVersion(), $targetVersion, '>');
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
         * @var EntityManagerInterface $entityManager
         */
        $entityManager = $this->container->get('models');
        $repository = $entityManager->getRepository(Config::class);

        /**
         * @var Config $element
         */
        foreach ($repository->findAll() as $element) {
            if (false === stripos($element->getName(), 'LastChangeDateTime')) {
                continue;
            }

            $entityManager->remove($element);
        }

        $entityManager->flush();
    }

    private function clearOldDatabaseTables()
    {
        $tables = [
            'plenty_log',
            'plenty_config',
            'plenty_mapping_attribute_group',
            'plenty_mapping_attribute_option',
            'plenty_mapping_category',
            'plenty_mapping_category_old',
            'plenty_mapping_country',
            'plenty_mapping_currency',
            'plenty_mapping_customer',
            'plenty_mapping_customer_billing_address',
            'plenty_mapping_customer_class',
            'plenty_mapping_item',
            'plenty_mapping_item_bundle',
            'plenty_mapping_item_variant',
            'plenty_mapping_measure_unit',
            'plenty_mapping_method_of_payment',
            'plenty_mapping_order_status',
            'plenty_mapping_payment_status',
            'plenty_mapping_producer',
            'plenty_mapping_property',
            'plenty_mapping_property_group',
            'plenty_mapping_referrer',
            'plenty_mapping_shipping_profile',
            'plenty_mapping_shop',
            'plenty_mapping_vat',
        ];

        /**
         * @var Connection $connection
         */
        $connection = $this->container->get('dbal_connection');

        foreach ($tables as $table) {
            try {
                $query = 'DROP TABLE IF EXISTS ?';

                $connection->query($query, [$table]);
            } catch (\Exception $exception) {
                // fail silently
            }
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param $filename
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
