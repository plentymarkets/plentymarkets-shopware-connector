<?php

namespace PlentyConnector\Connector\QueryBus\QueryHandler;

use Exception;
use PlentyConnector\Connector\QueryBus\Query\QueryInterface;

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
     * @return mixed
     *
     * @throws Exception
     */
    public function handle(QueryInterface $query);
}
