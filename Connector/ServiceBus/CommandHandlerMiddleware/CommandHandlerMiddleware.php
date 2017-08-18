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
     * {@inheritdoc}
     */
    public function execute($command, callable $next)
    {
        if (null === $this->handlers) {
            return $next($command);
        }

        if (!($command instanceof CommandInterface)) {
            return $next($command);
        }

        $handlers = array_filter($this->handlers, function (CommandHandlerInterface $handler) use ($command) {
            return $handler->supports($command);
        });

        if (0 === count($handlers)) {
            throw NotFoundException::fromCommand($command);
        }

        foreach ($handlers as $handler) {
            $handler->handle($command);
        }

        return $next($command);
    }
}
