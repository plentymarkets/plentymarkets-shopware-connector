<?php

namespace PlentyConnector\Connector\ServiceBus\QueryHandler;

use Exception;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;

/**
 * Interface QueryHandlerInterface.
 */
interface QueryHandlerInterface
{
    /**
     * @param QueryInterface $query
     *
     * @return bool
     */
    public function supports(QueryInterface $query);

    /**
     * @param QueryInterface $query
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function handle(QueryInterface $query);
}
