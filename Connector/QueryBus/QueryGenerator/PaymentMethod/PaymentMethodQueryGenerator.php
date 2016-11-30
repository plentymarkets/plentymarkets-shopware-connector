<?php

namespace PlentyConnector\Connector\QueryBus\QueryGenerator\PaymentMethod;

use PlentyConnector\Connector\QueryBus\Query\PaymentMethod\FetchAllPaymentMethodsQuery;
use PlentyConnector\Connector\QueryBus\Query\PaymentMethod\FetchChangedPaymentMethodsQuery;
use PlentyConnector\Connector\QueryBus\Query\PaymentMethod\FetchPaymentMethodQuery;
use PlentyConnector\Connector\QueryBus\Query\QueryInterface;
use PlentyConnector\Connector\QueryBus\QueryGenerator\QueryGeneratorInterface;
use PlentyConnector\Connector\TransferObject\TransferObjectType;

/**
 * Class PaymentMethodQueryGenerator
 */
class PaymentMethodQueryGenerator implements QueryGeneratorInterface
{
    /**
     * @param string $transferObjectType
     *
     * @return boolean
     */
    public function supports($transferObjectType)
    {
        return $transferObjectType === TransferObjectType::PAYMENT_METHOD;
    }

    /**
     * @param string $adapterName
     *
     * @return QueryInterface
     */
    public function generateFetchChangedQuery($adapterName)
    {
        return new FetchChangedPaymentMethodsQuery($adapterName);
    }

    /**
     * @param string $adapterName
     *
     * @return QueryInterface
     */
    public function generateFetchAllQuery($adapterName)
    {
        return new FetchAllPaymentMethodsQuery($adapterName);
    }

    /**
     * @param string $adapterName
     * @param string $identifier
     *
     * @return QueryInterface
     */
    public function generateFetchQuery($adapterName, $identifier)
    {
        return new FetchPaymentMethodQuery($adapterName, $identifier);
    }
}
