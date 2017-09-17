<?php

namespace PlentyConnector\Subscriber;

use Enlight\Event\SubscriberInterface;
use Enlight_Components_Cron_EventArgs as Args;
use Exception;
use PlentyConnector\Connector\BacklogService\BacklogServiceInterface;
use PlentyConnector\Connector\CleanupService\CleanupServiceInterface;
use PlentyConnector\Connector\ConnectorInterface;
use PlentyConnector\Connector\ServiceBus\QueryType;
use PlentyConnector\Connector\ServiceBus\ServiceBusInterface;
use PlentyConnector\PlentyConnector;
use Psr\Log\LoggerInterface;

/**
 * Class CronjobSubscriber
 */
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
     * CronjobSubscriber constructor.
     *
     * @param ConnectorInterface      $connector
     * @param CleanupServiceInterface $cleanupService
     * @param BacklogServiceInterface $backlogService
     * @param ServiceBusInterface     $serviceBus
     * @param LoggerInterface         $logger
     */
    public function __construct(
        ConnectorInterface $connector,
        CleanupServiceInterface $cleanupService,
        BacklogServiceInterface $backlogService,
        ServiceBusInterface $serviceBus,
        LoggerInterface $logger
    ) {
        $this->connector = $connector;
        $this->cleanupService = $cleanupService;
        $this->backlogService = $backlogService;
        $this->serviceBus = $serviceBus;
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
    public function onRunCronjobProcessBacklog(Args $args)
    {
        try {
            $counter = 0;
            while ($counter < 200 && $command = $this->backlogService->dequeue()) {
                ++$counter;

                $this->serviceBus->handle($command);
            }
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
