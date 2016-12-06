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
     * {@inheritdoc}
     */
    public function supports($transferObjectType)
    {
        return $transferObjectType === TransferObjectType::PAYMENT_METHOD;
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchChangedQuery($adapterName)
    {
        return new FetchChangedPaymentMethodsQuery($adapterName);
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchAllQuery($adapterName)
    {
        return new FetchAllPaymentMethodsQuery($adapterName);
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchQuery($adapterName, $identifier)
    {
        return new FetchPaymentMethodQuery($adapterName, $identifier);
    }
}
