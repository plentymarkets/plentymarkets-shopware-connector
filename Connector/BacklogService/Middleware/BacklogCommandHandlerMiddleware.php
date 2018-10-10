<?php

namespace SystemConnector\BacklogService\Middleware;

use League\Tactician\Middleware;
use Psr\Log\LoggerInterface;
use SystemConnector\BacklogService\BacklogServiceInterface;
use SystemConnector\BacklogService\Command\HandleBacklogElementCommand;
use SystemConnector\ServiceBus\Command\CommandInterface;
use SystemConnector\ServiceBus\Command\TransferObjectCommand;
use SystemConnector\TransferObject\TransferObjectInterface;

class BacklogCommandHandlerMiddleware implements Middleware
{
    /**
     * flag to enable or disable the whole backlog functionality
     *
     * @var bool
     */
    public static $active = true;

    /**
     * @var BacklogServiceInterface
     */
    private $backlogService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(BacklogServiceInterface $backlogService, LoggerInterface $logger)
    {
        $this->backlogService = $backlogService;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function execute($command, callable $next)
    {
        if (!self::$active) {
            return $next($command);
        }

        if ($command instanceof HandleBacklogElementCommand) {
            $command = $command->getPayload();

            return $next($command);
        }

        if ($command instanceof CommandInterface) {
            $this->backlogService->enqueue($command);

            $this->logCommandEnqueued($command);

            return true;
        }

        return $next($command);
    }

    /**
     * @param CommandInterface $command
     */
    private function logCommandEnqueued(CommandInterface $command)
    {
        $context = [];

        if ($command instanceof TransferObjectCommand) {
            $context['adapterName'] = $command->getAdapterName();
            $context['objectType'] = $command->getObjectType();
            $context['commandType'] = $command->getCommandType();

            if ($command->getPayload() instanceof TransferObjectInterface) {
                $context['transferObject'] = $command->getPayload()->getIdentifier();
            } else {
                $context['transferObject'] = $command->getPayload();
            }
        }

        $this->logger->debug('Command enqueued', $context);
    }
}
