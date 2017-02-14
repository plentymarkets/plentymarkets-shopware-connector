<?php

namespace PlentyConnector\Connector\ServiceBus\QueryGenerator\CustomerGroup;

use PlentyConnector\Connector\ServiceBus\Query\CustomerGroup\FetchAllCustomerGroupsQuery;
use PlentyConnector\Connector\ServiceBus\Query\CustomerGroup\FetchChangedCustomerGroupsQuery;
use PlentyConnector\Connector\ServiceBus\Query\CustomerGroup\FetchCustomerGroupQuery;
use PlentyConnector\Connector\ServiceBus\QueryGenerator\QueryGeneratorInterface;
use PlentyConnector\Connector\TransferObject\CustomerGroup\CustomerGroup;

/**
 * Class CustomerGroupQueryGenerator
 */
class CustomerGroupQueryGenerator implements QueryGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($transferObjectType)
    {
        return $transferObjectType === CustomerGroup::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchAllQuery($adapterName)
    {
        return new FetchAllCustomerGroupsQuery($adapterName);
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchChangedQuery($adapterName)
    {
        return new FetchChangedCustomerGroupsQuery($adapterName);
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchQuery($adapterName, $identifier)
    {
        return new FetchCustomerGroupQuery($adapterName, $identifier);
    }
}
