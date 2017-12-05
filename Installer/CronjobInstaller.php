<?php

namespace PlentyConnector\Installer;

use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\InvalidArgumentException;
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

    /**
     * @var array
     */
    private $cronjobs;

    /**
     * DatabaseInstaller constructor.
     *
     * @param Connection $connection
     * @param array      $cronjobs
     */
    public function __construct(Connection $connection, array $cronjobs)
    {
        $this->connection = $connection;
        $this->cronjobs   = $cronjobs;
    }

    /**
     * @param InstallContext $context
     *
     * @throws InvalidArgumentException
     */
    public function install(InstallContext $context)
    {
        $this->removeCronjobs();

        foreach ($this->cronjobs as $name => $interval) {
            $this->addCronjob($name, $interval, $context->getPlugin()->getId());
        }
    }

    /**
     * @param UpdateContext $context
     *
     * @throws InvalidArgumentException
     */
    public function update(UpdateContext $context)
    {
        $this->removeCronjobs($context->getPlugin()->getId());

        foreach ($this->cronjobs as $name => $interval) {
            $this->addCronjob($name, $interval, $context->getPlugin()->getId());
        }
    }

    /**
     * @param UninstallContext $context
     *
     * @throws InvalidArgumentException
     */
    public function uninstall(UninstallContext $context)
    {
        $this->removeCronjobs($context->getPlugin()->getId());
    }

    /**
     * @param null|int $pluginIdentifier
     *
     * @throws InvalidArgumentException
     */
    private function removeCronjobs($pluginIdentifier = null)
    {
        if (null !== $pluginIdentifier) {
            $this->connection->delete('s_crontab', ['pluginID' => $pluginIdentifier]);
        }

        foreach ($this->cronjobs as $name => $interval) {
            $this->connection->delete('s_crontab', ['name' => 'PlentyConnector ' . $name]);
        }
    }

    /**
     * @param string $name
     * @param int    $interval
     * @param int    $pluginIdentifier
     */
    private function addCronjob($name, $interval, $pluginIdentifier)
    {
        $data = [
            'name'             => 'PlentyConnector ' . $name,
            'action'           => 'Shopware_CronJob_PlentyConnector' . $name,
            'next'             => new DateTime(),
            'start'            => null,
            '`interval`'       => $interval,
            'active'           => true,
            'disable_on_error' => true,
            'end'              => new DateTime(),
            'pluginID'         => $pluginIdentifier,
        ];

        $types = [
            'next' => 'datetime',
            'end'  => 'datetime',
        ];

        $this->connection->insert('s_crontab', $data, $types);
    }
}
