<?php

namespace PlentyConnector\Connector\QueryBus\QueryGenerator\CustomerGroup;

use PlentyConnector\Connector\QueryBus\Query\CustomerGroup\FetchAllCustomerGroupsQuery;
use PlentyConnector\Connector\QueryBus\Query\CustomerGroup\FetchChangedCustomerGroupsQuery;
use PlentyConnector\Connector\QueryBus\Query\CustomerGroup\FetchCustomerGroupQuery;
use PlentyConnector\Connector\QueryBus\QueryGenerator\QueryGeneratorInterface;
use PlentyConnector\Connector\TransferObject\TransferObjectType;

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
        return $transferObjectType === TransferObjectType::CUSTOMER_GROUP;
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
    public function generateFetchAllQuery($adapterName)
    {
        return new FetchAllCustomerGroupsQuery($adapterName);
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchQuery($adapterName, $identifier)
    {
        return new FetchCustomerGroupQuery($adapterName, $identifier);
    }
}
