<?php

namespace PlentyConnector\Connector\BacklogService\Middleware;

use League\Tactician\Middleware;
use PlentyConnector\Connector\BacklogService\BacklogServiceInterface;
use PlentyConnector\Connector\BacklogService\Command\HandleBacklogElementCommand;
use PlentyConnector\Connector\ServiceBus\Command\CommandInterface;

/**
 * Class BacklogCommandHandler
 */
class BacklogCommandHandlerMiddleware implements Middleware
{
    /**
     * flag to enable or disable the whole backlog functionality
     *
     * @var bool
     */
    static public $active = true;

    /**
     * @var BacklogServiceInterface
     */
    private $backlogService;

    /**
     * BacklogMiddleware constructor.
     *
     * @param BacklogServiceInterface $backlogService
     */
    public function __construct(BacklogServiceInterface $backlogService)
    {
        $this->backlogService = $backlogService;
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
            $command = $command->getCommand();

            return $next($command);
        }

        if ($command instanceof CommandInterface) {
            $this->backlogService->enqueue($command);

            return true;
        }

        return $next($command);
    }
}
