<?php

namespace PlentyConnector\Connector\ServiceBus\QueryHandler;

use Exception;
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
     * @throws Exception
     *
     * @return TransferObjectInterface[]
     */
    public function handle(QueryInterface $query);
}
