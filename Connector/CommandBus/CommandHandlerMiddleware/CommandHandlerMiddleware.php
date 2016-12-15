<?php

namespace PlentyConnector\Connector\CommandBus\CommandHandlerMiddleware;

use League\Tactician\Middleware;
use PlentyConnector\Connector\CommandBus\Command\CommandInterface;
use PlentyConnector\Connector\CommandBus\CommandHandler\CommandHandlerInterface;

/**
 * Class CommandHandlerMiddleware.
 */
class CommandHandlerMiddleware implements Middleware
{
    /**
     * @var CommandHandlerInterface[]
     */
    private $handlers;

    /**
     * @param CommandHandlerInterface $handler
     */
    public function addHandler(CommandHandlerInterface $handler)
    {
        $this->handlers[] = $handler;
    }

    /**
     * @param CommandInterface $command
     * @param callable $next
     *
     * @return mixed
     *
     * @throws NoHandlerException
     */
    public function execute($command, callable $next)
    {
        if (null === $this->handlers) {
            return $next($command);
        }

        $handlers = array_filter($this->handlers, function (CommandHandlerInterface $handler) use ($command) {
            return $handler->supports($command);
        });

        if (null === $this->handlers) {
            return $next($command);
        }

        array_map(function (CommandHandlerInterface $handler) use ($command) {
            $handler->handle($command);
        }, $handlers);

        return $next($command);
    }
}
