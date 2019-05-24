<?php

namespace PlentyConnector\Subscriber;

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
    public static function getSubscribedEvents() :array
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
    public function onRunCronjobSynchronize(Args $args): bool
    {
        try {
            $this->connector->handle(QueryType::CHANGED);
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
        } catch (Throwable $exception) {
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
    public function onRunCronjobProcessBacklog(Args $args): bool
    {
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

        $args->setReturn(true);

        return true;
    }

    /**
     * @param Args $args
     *
     * @return bool
     */
    public function onRunCronjobCleanup(Args $args): bool
    {
        try {
            $this->cleanupService->cleanup();
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
        } catch (Throwable $exception) {
            $this->logger->error($exception->getMessage());
        }

        $args->setReturn(true);

        return true;
    }
}
