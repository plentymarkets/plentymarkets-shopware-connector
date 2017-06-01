<?php

namespace PlentyConnector\Connector\ServiceBus\QueryHandler;

use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\TransferObject\TransferObjectInterface;

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
     * @return TransferObjectInterface[]
     */
    public function handle(QueryInterface $query);
}
