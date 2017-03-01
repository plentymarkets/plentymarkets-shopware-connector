<?php

namespace PlentyConnector\Connector\ServiceBus\QueryGenerator\Order;

use PlentyConnector\Connector\ServiceBus\Query\Order\FetchAllOrdersQuery;
use PlentyConnector\Connector\ServiceBus\Query\Order\FetchChangedOrdersQuery;
use PlentyConnector\Connector\ServiceBus\Query\Order\FetchOrderQuery;
use PlentyConnector\Connector\ServiceBus\QueryGenerator\QueryGeneratorInterface;
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
    public function generateFetchAllQuery($adapterName)
    {
        return new FetchAllOrdersQuery($adapterName);
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
    public function generateFetchQuery($adapterName, $identifier)
    {
        return new FetchOrderQuery($adapterName, $identifier);
    }
}
