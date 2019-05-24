<?php

namespace SystemConnector\ServiceBus\QueryHandler;

use SystemConnector\ServiceBus\Query\QueryInterface;
use SystemConnector\TransferObject\TransferObjectInterface;

interface QueryHandlerInterface
{
    /**
     * @param QueryInterface $query
     *
     * @return bool
     */
    public function supports(QueryInterface $query) :bool;

    /**
     * @param QueryInterface $query
     *
     * @return TransferObjectInterface[]
     */
    public function handle(QueryInterface $query);
}
