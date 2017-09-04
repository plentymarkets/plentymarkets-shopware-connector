<?php

namespace PlentyConnector\Connector\ServiceBus\QueryGenerator\Variation;

use PlentyConnector\Connector\ServiceBus\Query\Variation\FetchAllVariationsQuery;
use PlentyConnector\Connector\ServiceBus\Query\Variation\FetchChangedVariationsQuery;
use PlentyConnector\Connector\ServiceBus\Query\Variation\FetchVariationQuery;
use PlentyConnector\Connector\ServiceBus\QueryGenerator\QueryGeneratorInterface;
use PlentyConnector\Connector\TransferObject\Product\Variation\Variation;

/**
 * Class VariationQueryGenerator
 */
class VariationQueryGenerator implements QueryGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($transferObjectType)
    {
        return $transferObjectType === Variation::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchAllQuery($adapterName)
    {
        return new FetchAllVariationsQuery($adapterName);
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchChangedQuery($adapterName)
    {
        return new FetchChangedVariationsQuery($adapterName);
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchQuery($adapterName, $identifier)
    {
        return new FetchVariationQuery($adapterName, $identifier);
    }
}
