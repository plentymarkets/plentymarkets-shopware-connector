<?php

namespace PlentyConnector\Subscriber;

use Enlight\Event\SubscriberInterface;
use Enlight_Event_EventArgs as Args;
use Exception;
use PlentyConnector\Connector\CleanupService\CleanupServiceInterface;
use PlentyConnector\Connector\ConnectorInterface;
use PlentyConnector\Connector\QueryBus\QueryType;
use Psr\Log\LoggerInterface;

/**
 * Class CronjobSubscriper
 */
class CronjobSubscriper implements SubscriberInterface
{
    const CRONJOB_SYNCHRONIZE = 'Synchronize';
    const CRONJOB_CLEANUP = 'Cleanup';

    /**
     * pre defined list to invalidate simple caches
     */
    const CRONJOBS = [
        self::CRONJOB_SYNCHRONIZE => 300,
        self::CRONJOB_CLEANUP => 86400,
    ];

    /**
     * @var ConnectorInterface
     */
    private $connector;

    /**
     * @var CleanupServiceInterface
     */
    private $cleanupService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * CronjobSubscriper constructor.
     *
     * @param ConnectorInterface $connector
     * @param CleanupServiceInterface $cleanupService
     * @param LoggerInterface $logger
     */
    public function __construct(
        ConnectorInterface $connector,
        CleanupServiceInterface $cleanupService,
        LoggerInterface $logger
    ) {
        $this->connector = $connector;
        $this->cleanupService = $cleanupService;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Shopware_CronJob_PlentyConnector' . self::CRONJOB_SYNCHRONIZE => 'onRunCronjobSynchronize',
            'Shopware_CronJob_PlentyConnector' . self::CRONJOB_CLEANUP => 'onRunCronjobCleanup'
        ];
    }

    /**
     * @param Args $args
     *
     * @return bool
     */
    public function onRunCronjobSynchronize(Args $args)
    {
        try {
            $this->connector->handle(QueryType::CHANGED);
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
        }

        return true;
    }

    /**
     * @param Args $args
     *
     * @return bool
     */
    public function onRunCronjobCleanup(Args $args)
    {
        try {
            $this->cleanupService->cleanup();
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
        }

        return true;
    }
}
