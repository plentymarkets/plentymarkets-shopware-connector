<?php

namespace PlentyConnector\Connector\ServiceBus\EventHandler;

use PlentyConnector\Connector\ServiceBus\Event\EventInterface;

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
