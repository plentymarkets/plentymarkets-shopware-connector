<?php

namespace PlentyConnector\Subscriber;

use Doctrine\DBAL\Connection;
use Enlight\Event\SubscriberInterface;
use Enlight_Components_Cron_EventArgs as Args;
use Exception;
use PlentyConnector\PlentyConnector;
use Psr\Log\LoggerInterface;
use SystemConnector\BacklogService\BacklogServiceInterface;
use SystemConnector\CleanupService\CleanupServiceInterface;
use SystemConnector\ConnectorInterface;
use SystemConnector\ServiceBus\QueryType;
use SystemConnector\ServiceBus\ServiceBusInterface;
use Throwable;

class CronjobSubscriber implements SubscriberInterface
{
    /**
     * @var ConnectorInterface
     */
    private $connector;

    /**
     * @var CleanupServiceInterface
     */
    private $cleanupService;

    /**
     * @var BacklogServiceInterface
     */
    private $backlogService;

    /**
     * @var ServiceBusInterface
     */
    private $serviceBus;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(
        ConnectorInterface $connector,
        CleanupServiceInterface $cleanupService,
        BacklogServiceInterface $backlogService,
        ServiceBusInterface $serviceBus,
        LoggerInterface $logger,
        Connection $connection
    ) {
        $this->connector = $connector;
        $this->cleanupService = $cleanupService;
        $this->backlogService = $backlogService;
        $this->serviceBus = $serviceBus;
        $this->logger = $logger;
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Shopware_CronJob_PlentyConnector' . PlentyConnector::CRONJOB_SYNCHRONIZE => 'onRunCronjobSynchronize',
            'Shopware_CronJob_PlentyConnector' . PlentyConnector::CRONJOB_CLEANUP => 'onRunCronjobCleanup',
            'Shopware_CronJob_PlentyConnector' . PlentyConnector::CRONJOB_BACKLOG => 'onRunCronjobProcessBacklog',
        ];
    }

    /**
     * @param Args $args
     *
     * @return bool
     */
    public function onRunCronjobSynchronize(Args $args)
    {
        $cronjobState = $this->checkCronjobState(['Shopware_CronJob_PlentyConnector' . PlentyConnector::CRONJOB_BACKLOG]);

        if ($cronjobState) {
            try {
                $this->connector->handle(QueryType::CHANGED);
            } catch (Exception $exception) {
                $this->logger->error($exception->getMessage());
            } catch (Throwable $exception) {
                $this->logger->error($exception->getMessage());
            }
        }

        $args->setReturn(true);

        return true;
    }

    /**
     * @param Args $args
     *
     * @return bool
     */
    public function onRunCronjobProcessBacklog(Args $args)
    {
        $cronjobState = $this->checkCronjobState(['Shopware_CronJob_PlentyConnector' . PlentyConnector::CRONJOB_SYNCHRONIZE]);

        if ($cronjobState) {
            try {
                $counter = 0;
                while ($counter < 200 && $command = $this->backlogService->dequeue()) {
                    ++$counter;

                    $this->serviceBus->handle($command);
                }
            } catch (Exception $exception) {
                $this->logger->error($exception->getMessage());
            } catch (Throwable $exception) {
                $this->logger->error($exception->getMessage());
            }
        }

        $args->setReturn(true);

        return true;
    }

    /**
     * @param Args $args
     *
     * @return bool
     */
    public function onRunCronjobCleanup(Args $args)
    {
        $cronjobState = $this->checkCronjobState(['Shopware_CronJob_PlentyConnector' . PlentyConnector::CRONJOB_BACKLOG, 'Shopware_CronJob_PlentyConnector' . PlentyConnector::CRONJOB_SYNCHRONIZE]);

        if ($cronjobState) {
            try {
                $this->cleanupService->cleanup();
            } catch (Exception $exception) {
                $this->logger->error($exception->getMessage());
            } catch (Throwable $exception) {
                $this->logger->error($exception->getMessage());
            }
        }

        $args->setReturn(true);

        return true;
    }

    /**
     * @param array $cronjobs
     *
     * @return bool
     */
    private function checkCronjobState($cronjobs = [])
    {
        if (empty($cronjobs)) {
            return false;
        }

        foreach ($cronjobs as $cronjob) {
            $state = $this->connection->fetchColumn('SELECT active FROM `s_crontab` WHERE action = ?', [$cronjob]);

            if ($state) {
                return true;
            }
        }

        return false;
    }
}
