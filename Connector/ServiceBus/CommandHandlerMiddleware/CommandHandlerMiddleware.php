<?php

namespace PlentyConnector\Connector\ServiceBus\CommandHandlerMiddleware;

use League\Tactician\Middleware;
use PlentyConnector\Connector\ServiceBus\Command\CommandInterface;
use PlentyConnector\Connector\ServiceBus\CommandHandler\CommandHandlerInterface;
use PlentyConnector\Connector\ServiceBus\CommandHandlerMiddleware\Exception\NotFoundException;

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
     * @param callable         $next
     *
     * @throws NotFoundException
     *
     * @return mixed
     */
    public function execute($command, callable $next)
    {
        if (null === $this->handlers) {
            return $next($command);
        }

        $handlers = array_filter($this->handlers, function (CommandHandlerInterface $handler) use ($command) {
            if (!($command instanceof CommandInterface)) {
                return false;
            }

            return $handler->supports($command);
        });

        if (0 === count($handlers)) {
            if ($command instanceof CommandInterface) {
                throw NotFoundException::fromCommand($command);
            }

            return $next($command);
        }

        array_map(function (CommandHandlerInterface $handler) use ($command) {
            $handler->handle($command);
        }, $handlers);

        return $next($command);
    }
}
