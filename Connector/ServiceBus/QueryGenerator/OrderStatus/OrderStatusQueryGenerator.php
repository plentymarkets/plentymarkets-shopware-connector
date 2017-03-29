<?php

namespace PlentyConnector\Connector\ServiceBus\QueryGenerator\OrderStatus;

use PlentyConnector\Connector\ServiceBus\Query\OrderStatus\FetchAllOrderStatusesQuery;
use PlentyConnector\Connector\ServiceBus\Query\OrderStatus\FetchChangedOrderStatusesQuery;
use PlentyConnector\Connector\ServiceBus\Query\OrderStatus\FetchOrderStatusQuery;
use PlentyConnector\Connector\ServiceBus\QueryGenerator\QueryGeneratorInterface;
use PlentyConnector\Connector\TransferObject\OrderStatus\OrderStatus;

/**
 * Class ManufacturerQueryGenerator.
 */
class OrderStatusQueryGenerator implements QueryGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($transferObjectType)
    {
        return $transferObjectType === OrderStatus::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchAllQuery($adapterName)
    {
        return new FetchAllOrderStatusesQuery($adapterName);
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchChangedQuery($adapterName)
    {
        return new FetchChangedOrderStatusesQuery($adapterName);
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchQuery($adapterName, $identifier)
    {
        return new FetchOrderStatusQuery($adapterName, $identifier);
    }
}
