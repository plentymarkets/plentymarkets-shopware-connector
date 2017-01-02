<?php

namespace PlentyConnector\Installer;

use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Exception;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use Shopware\Components\Plugin\Context\UpdateContext;

/**
 * Class CronjobInstaller
 */
class CronjobInstaller implements InstallerInterface
{
    /**
     * @var Connection
     */
    private $connection;

    private $cronjobs = [
        'Synchronize' => 300,
        'Cleanup' => 86400
    ];

    /**
     * DatabaseInstaller constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function install(InstallContext $context)
    {
        foreach ($this->cronjobs as $name => $interval) {
            try {
                $this->addCronjob($name, $interval);
            } catch (Exception $exception) {
                // fail silently
            }
        }
    }

    /**
     * @param $name
     * @param $interval
     */
    private function addCronjob($name, $interval)
    {
        $data = [
            'name' => $name,
            'action' => 'Shopware_CronJob_' . $name,
            'next' => new DateTime(),
            'start' => null,
            'interval' => $interval,
            'active' => 1,
            'end' => new DateTime(),
            'pluginID' => null
        ];

        $types = [
            'next' => 'datetime',
            'end' => 'datetime',
        ];

        $this->connection->insert('s_crontab', $data, $types);
    }

    /**
     * {@inheritdoc}
     */
    public function update(UpdateContext $context)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function uninstall(UninstallContext $context)
    {
        foreach ($this->cronjobs as $name => $interval) {
            try {
                $this->removeCronjob($name);
            } catch (Exception $exception) {
                // fail silently
            }
        }
    }

    /**
     * @param $name
     *
     * @throws DBALException
     */
    private function removeCronjob($name)
    {
        $this->connection->executeQuery('DELETE FROM s_crontab WHERE `name` = ?', $name);
    }
}
