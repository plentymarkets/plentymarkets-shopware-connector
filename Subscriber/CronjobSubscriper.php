<?php

namespace PlentyConnector\Subscriber;

use Enlight\Event\SubscriberInterface;
use Enlight_Components_Cron_EventArgs as Args;
use Exception;
use PlentyConnector\Connector\CleanupService\CleanupServiceInterface;
use PlentyConnector\Connector\ConnectorInterface;
use PlentyConnector\Connector\ServiceBus\QueryType;
use PlentyConnector\PlentyConnector;
use Psr\Log\LoggerInterface;

/**
 * Class CronjobSubscriper
 */
class CronjobSubscriper implements SubscriberInterface
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
            'Shopware_CronJob_PlentyConnector' . PlentyConnector::CRONJOB_SYNCHRONIZE => 'onRunCronjobSynchronize',
            'Shopware_CronJob_PlentyConnector' . PlentyConnector::CRONJOB_CLEANUP => 'onRunCronjobCleanup',
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
        try {
            $this->cleanupService->cleanup();
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
        }

        $args->setReturn(true);

        return true;
    }
}
