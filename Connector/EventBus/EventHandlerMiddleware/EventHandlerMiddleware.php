<?php

namespace PlentyConnector\Connector\EventBus\EventHandlerMiddleware;

use League\Tactician\Middleware;
use PlentyConnector\Connector\EventBus\Event\EventInterface;
use PlentyConnector\Connector\EventBus\EventHandler\EventHandlerInterface;

/**
 * Class EventHandlerMiddleware.
 */
class EventHandlerMiddleware implements Middleware
{
    /**
     * @var EventHandlerInterface[]
     */
    private $handlers;

    /**
     * @param EventHandlerInterface $handler
     */
    public function addHandler(EventHandlerInterface $handler)
    {
        $this->handlers[] = $handler;
    }

    /**
     * @param EventInterface $event
     * @param callable $next
     *
     * @return mixed
     */
    public function execute($event, callable $next)
    {
        if (null === $this->handlers) {
            return $next($event);
        }

        $handlers = array_filter($this->handlers, function (EventHandlerInterface $handler) use ($event) {
            return $handler->supports($event);
        });

        array_walk($handlers, function (EventHandlerInterface $handler) use ($event) {
            $handler->handle($event);
        });

        return $next($event);
    }
}
