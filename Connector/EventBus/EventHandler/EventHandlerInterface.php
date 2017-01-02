<?php

namespace PlentyConnector\Connector\EventBus\EventHandler;

use PlentyConnector\Connector\EventBus\Event\EventInterface;

/**
 * Interface EventHandlerInterface.
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
