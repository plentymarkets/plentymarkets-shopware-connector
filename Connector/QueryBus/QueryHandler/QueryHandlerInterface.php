<?php

namespace PlentyConnector\Connector\QueryBus\QueryHandler;

use PlentyConnector\Connector\QueryBus\Query\QueryInterface;

/**
 * Interface QueryHandlerInterface.
 */
interface QueryHandlerInterface
{
    /**
     * @param QueryInterface $event
     *
     * @return bool
     */
    public function supports(QueryInterface $event);

    /**
     * @param QueryInterface $event
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function handle(QueryInterface $event);
}
