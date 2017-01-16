<?php

namespace PlentyConnector\Connector\QueryBus\QueryGenerator\Order;

use PlentyConnector\Connector\QueryBus\Query\FetchChangedOrdersQuery;
use PlentyConnector\Connector\QueryBus\Query\FetchOrderQuery;
use PlentyConnector\Connector\QueryBus\Query\Order\FetchAllOrdersQuery;
use PlentyConnector\Connector\QueryBus\QueryGenerator\QueryGeneratorInterface;
use PlentyConnector\Connector\TransferObject\Order\Order;

class OrderQueryGenerator implements QueryGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($transferObjectType)
    {
        return $transferObjectType === Order::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchChangedQuery($adapterName)
    {
        return new FetchChangedOrdersQuery($adapterName);
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchAllQuery($adapterName)
    {
        return new FetchAllOrdersQuery($adapterName);
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchQuery($adapterName, $identifier)
    {
        return new FetchOrderQuery($adapterName, $identifier);
    }
}
