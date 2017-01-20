<?php

namespace PlentyConnector\Connector\ServiceBus\QueryGenerator\PaymentMethod;

use PlentyConnector\Connector\ServiceBus\Query\PaymentMethod\FetchAllPaymentMethodsQuery;
use PlentyConnector\Connector\ServiceBus\Query\PaymentMethod\FetchChangedPaymentMethodsQuery;
use PlentyConnector\Connector\ServiceBus\Query\PaymentMethod\FetchPaymentMethodQuery;
use PlentyConnector\Connector\ServiceBus\QueryGenerator\QueryGeneratorInterface;
use PlentyConnector\Connector\TransferObject\PaymentMethod\PaymentMethod;

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
        return $transferObjectType === PaymentMethod::TYPE;
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
