<?php

namespace PlentyConnector\Connector\EventBus\Handler;

use PlentyConnector\Connector\EventBus\Event\EventInterface;

/**
 * Interface EventHandlerInterface
 *
 * @package PlentyConnector\Connector\EventBus\Handler
 */
interface EventHandlerInterface
{
    /**
     * @param EventInterface $event
     *
     * @return bool
     */
    public function supports(EventInterface $event);

    /**
     * @param EventInterface $event
     */
    public function handle(EventInterface $event);
}
