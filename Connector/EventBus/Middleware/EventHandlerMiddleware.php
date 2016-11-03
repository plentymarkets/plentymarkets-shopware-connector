<?php

namespace PlentyConnector\Connector\EventBus\Middleware;

use League\Tactician\Middleware;
use PlentyConnector\Connector\EventBus\Event\EventInterface;
use PlentyConnector\Connector\EventBus\Handler\EventHandlerInterface;

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
     */
    public function execute($event, callable $next)
    {
        $handlers = array_filter($this->handlers, function (EventHandlerInterface $handler) use ($event) {
            return $handler->supports($event);
        });

        array_map(function (EventHandlerInterface $handler) use ($event) {
            $handler->handle($event);
        }, $handlers);

        $next($event);
    }
}
