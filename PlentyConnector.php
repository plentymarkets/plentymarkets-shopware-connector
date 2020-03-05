<?php

namespace PlentyConnector;

use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use PlentyConnector\Installer\CronjobInstaller;
use PlentyConnector\Installer\DatabaseInstaller;
use PlentyConnector\Installer\Model\Backlog;
use PlentyConnector\Installer\Model\Config;
use PlentyConnector\Installer\Model\Identity;
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
use SystemConnector\BacklogService\BacklogService;
use SystemConnector\IdentityService\IdentityServiceInterface;
use SystemConnector\TransferObject\Category\Category;
use SystemConnector\TransferObject\PaymentStatus\PaymentStatus;

require __DIR__ . '/autoload.php';

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
    const CRONJOB_BACKLOG = 'ProcessBacklog';

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
        Backlog::class,
    ];

    /**
     * List of all cronjobs
     */
    public static $cronjobs = [
        self::CRONJOB_SYNCHRONIZE => 60,
        self::CRONJOB_BACKLOG => 60,
        self::CRONJOB_CLEANUP => 86400,
    ];

    public function build(ContainerBuilder $container)
    {
        $container->setParameter('plenty_connector.plugin_dir', $this->getPath());

        $this->loadFile($container, __DIR__ . '/DependencyInjection/services.xml');
        $this->loadFile($container, __DIR__ . '/DependencyInjection/definitions.xml');

        $this->loadFile($container, __DIR__ . '/Connector/DependencyInjection/services.xml');
        $this->loadFile($container, __DIR__ . '/Connector/DependencyInjection/commands.xml');
        $this->loadFile($container, __DIR__ . '/Connector/DependencyInjection/validators.xml');

        $this->loadFile($container, __DIR__ . '/Adapter/ShopwareAdapter/DependencyInjection/services.xml');
        $this->loadFile($container, __DIR__ . '/Adapter/PlentymarketsAdapter/DependencyInjection/services.xml');

        $this->loadFile($container, __DIR__ . '/Components/Sepa/DependencyInjection/services.xml');

        if ($this->pluginExists($container, ['SwagPaymentPaypal', 'SwagPaymentPayPalInstallments', 'SwagPaymentPaypalPlus', 'SwagPaymentPayPalUnified'])) {
            $this->loadFile($container, __DIR__ . '/Components/PayPal/DependencyInjection/services.xml');
        }

        if ($this->pluginExists($container, ['BestitAmazonPay'])) {
            $this->loadFile($container, __DIR__ . '/Components/AmazonPay/DependencyInjection/services.xml');
        }

        if ($this->pluginExists($container, ['SwagPaymentKlarnaKpm'])) {
            $this->loadFile($container, __DIR__ . '/Components/Klarna/DependencyInjection/services.xml');
        }

        if ($this->pluginExists($container, ['SwagBundle'])) {
            $this->loadFile($container, __DIR__ . '/Components/Bundle/DependencyInjection/services.xml');
        }

        if ($this->pluginExists($container, ['SwagCustomProducts'])) {
            $this->loadFile($container, __DIR__ . '/Components/CustomProducts/DependencyInjection/services.xml');
        }

        parent::build($container);
    }

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

        if ($this->updateNeeded($context, '4.0.0') && $this->updatePossible($context, '2.0.0')) {
            $this->clearLastChangedConfigEntries();
        }

        if ($this->updateNeeded($context, '4.0.4') && $this->updatePossible($context, '2.0.0')) {
            $this->updateBacklogTable();
        }

        if ($this->updateNeeded($context, '5.0.0') && $this->updatePossible($context, '4.0.0')) {
            $this->modifyLastChangedConfigEntries('-1 week');
            $this->clearBacklogTable();
            $this->repairTranslationTable();
        }

        parent::update($context);
    }

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

    public function activate(ActivateContext $context)
    {
        $context->scheduleClearCache(InstallContext::CACHE_LIST_ALL);

        parent::activate($context);
    }

    private function pluginExists(ContainerBuilder $container, array $plugins): bool
    {
        $folders = $container->getParameter('shopware.plugin_directories');

        foreach ($plugins as $pluginName) {
            foreach ($folders as $folder) {
                if (file_exists($folder . $pluginName)) {
                    return true;
                }

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
     * @param string $targetVersion
     *
     * @return mixed
     */
    private function updateNeeded(UpdateContext $context, $targetVersion)
    {
        return version_compare($context->getCurrentVersion(), $targetVersion, '<');
    }

    /**
     * @param string $targetVersion
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

    /**
     * @param string $diff
     *
     * @throws Exception
     */
    private function modifyLastChangedConfigEntries($diff)
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

            $date = new DateTimeImmutable();
            $element->setValue($date->modify($diff)->format(DATE_W3C));

            $entityManager->persist($element);
        }

        $entityManager->flush();
    }

    private function clearBacklogTable()
    {
        /**
         * @var Connection $connection
         */
        $connection = $this->container->get('dbal_connection');

        $query = 'TRUNCATE plenty_backlog';
        $connection->executeQuery($query);
    }

    private function updateBacklogTable()
    {
        /**
         * @var Connection $connection
         */
        $connection = $this->container->get('dbal_connection');

        $query = 'UPDATE plenty_backlog SET status = :statusNew WHERE status = :statusOld';
        $connection->executeQuery($query, [
            ':statusNew' => BacklogService::STATUS_OPEN,
            ':statusOld' => '',
        ]);
    }

    private function repairTranslationTable()
    {
        /**
         * @var Connection $connection
         */
        $connection = $this->container->get('dbal_connection');

        $query = 'UPDATE s_core_translations SET objectdata = REPLACE(objectdata, :pathOld, :pathNew)';

        $data = [
            [
                'pathOld' => 'O:55:"PlentyConnector\Connector\ValueObject\Identity\Identity',
                'pathNew' => 'O:45:"SystemConnector\ValueObject\Identity\Identity',
            ],
            [
                'pathOld' => 's:67:" PlentyConnector\Connector\ValueObject\Identity\Identity',
                'pathNew' => 's:57:" SystemConnector\ValueObject\Identity\Identity',
            ],
            [
                'pathOld' => 's:68:" PlentyConnector\Connector\ValueObject\Identity\Identity',
                'pathNew' => 's:58:" SystemConnector\ValueObject\Identity\Identity',
            ],
            [
                'pathOld' => 's:73:" PlentyConnector\Connector\ValueObject\Identity\Identity',
                'pathNew' => 's:63:" SystemConnector\ValueObject\Identity\Identity',
            ],
            [
                'pathOld' => 's:74:" PlentyConnector\Connector\ValueObject\Identity\Identity',
                'pathNew' => 's:64:" SystemConnector\ValueObject\Identity\Identity',
            ],
        ];

        foreach ($data as $datum) {
            $connection->executeQuery($query, [
                ':pathOld' => $datum['pathOld'],
                ':pathNew' => $datum['pathNew'],
            ]);
        }
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
            } catch (Exception $exception) {
                // fail silently
            }
        }
    }

    /**
     * @param string $filename
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
