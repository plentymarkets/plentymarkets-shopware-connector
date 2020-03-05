<?php

namespace SystemConnector\ServiceBus\QueryHandler;

use SystemConnector\ServiceBus\Query\QueryInterface;
use SystemConnector\TransferObject\TransferObjectInterface;

interface QueryHandlerInterface
{
    public function supports(QueryInterface $query): bool;

    /**
     * @return TransferObjectInterface[]
     */
    public function handle(QueryInterface $query);
}
