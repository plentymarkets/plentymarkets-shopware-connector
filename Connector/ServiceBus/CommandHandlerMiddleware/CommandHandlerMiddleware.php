<?php

namespace SystemConnector\ServiceBus\CommandHandlerMiddleware;

use League\Tactician\Middleware;
use SystemConnector\ServiceBus\Command\CommandInterface;
use SystemConnector\ServiceBus\CommandHandler\CommandHandlerInterface;
use SystemConnector\ServiceBus\CommandHandlerMiddleware\Exception\NotFoundException;
use Traversable;

class CommandHandlerMiddleware implements Middleware
{
    /**
     * @var CommandHandlerInterface[]|Traversable
     */
    private $handlers;

    /**
     * @param CommandHandlerInterface[]|Traversable $handlers
     */
    public function __construct(Traversable $handlers)
    {
        $this->handlers = iterator_to_array($handlers);
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

        $handlers = array_filter($this->handlers, static function (CommandHandlerInterface $handler) use ($command) {
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
