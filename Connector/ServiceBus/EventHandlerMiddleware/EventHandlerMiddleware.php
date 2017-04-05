<?php

namespace PlentyConnector\Connector\ServiceBus\EventHandlerMiddleware;

use League\Tactician\Middleware;
use PlentyConnector\Connector\ServiceBus\Event\EventInterface;
use PlentyConnector\Connector\ServiceBus\EventHandler\EventHandlerInterface;

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
     * @param callable       $next
     *
     * @return mixed
     */
    public function execute($event, callable $next)
    {
        if (null === $this->handlers) {
            return $next($event);
        }

        if (!($event instanceof EventInterface)) {
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
