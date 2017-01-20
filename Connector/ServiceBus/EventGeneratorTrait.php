<?php

namespace PlentyConnector\Connector\ServiceBus;

use PlentyConnector\Connector\ServiceBus\Event\EventInterface;

/**
 * Class GeneratorTrait.
 */
trait EventGeneratorTrait
{
    /**
     * Register an events.
     *
     * @var EventInterface[]
     */
    protected $events = [];

    /**
     * Release all events.
     *
     * @return EventInterface[]
     */
    public function releaseEvents()
    {
        $events = $this->events;

        $this->events = [];

        return $events;
    }

    /**
     * Add an event.
     *
     * @param EventInterface $event
     */
    protected function addEvent(EventInterface $event)
    {
        $this->events[] = $event;
    }
}
