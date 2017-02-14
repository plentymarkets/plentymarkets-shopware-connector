<?php

namespace PlentyConnector\Connector\ServiceBus\QueryGenerator\Shop;

use PlentyConnector\Connector\ServiceBus\Query\Shop\FetchAllShopsQuery;
use PlentyConnector\Connector\ServiceBus\Query\Shop\FetchChangedShopsQuery;
use PlentyConnector\Connector\ServiceBus\Query\Shop\FetchShopQuery;
use PlentyConnector\Connector\ServiceBus\QueryGenerator\QueryGeneratorInterface;
use PlentyConnector\Connector\TransferObject\Shop\Shop;

/**
 * Class ShopQueryGenerator
 */
class ShopQueryGenerator implements QueryGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($transferObjectType)
    {
        return $transferObjectType === Shop::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchAllQuery($adapterName)
    {
        return new FetchAllShopsQuery($adapterName);
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchChangedQuery($adapterName)
    {
        return new FetchChangedShopsQuery($adapterName);
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchQuery($adapterName, $identifier)
    {
        return new FetchShopQuery($adapterName, $identifier);
    }
}
