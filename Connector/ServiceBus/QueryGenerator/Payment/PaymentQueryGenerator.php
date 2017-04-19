<?php

namespace PlentyConnector\Connector\ServiceBus\QueryGenerator\Payment;

use PlentyConnector\Connector\ServiceBus\Query\Payment\FetchAllPaymentsQuery;
use PlentyConnector\Connector\ServiceBus\Query\Payment\FetchChangedPaymentsQuery;
use PlentyConnector\Connector\ServiceBus\Query\Payment\FetchPaymentQuery;
use PlentyConnector\Connector\ServiceBus\QueryGenerator\QueryGeneratorInterface;
use PlentyConnector\Connector\TransferObject\Payment\Payment;

/**
 * Class PaymentQueryGenerator
 */
class PaymentQueryGenerator implements QueryGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($transferObjectType)
    {
        return $transferObjectType === Payment::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchAllQuery($adapterName)
    {
        return new FetchAllPaymentsQuery($adapterName);
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchChangedQuery($adapterName)
    {
        return new FetchChangedPaymentsQuery($adapterName);
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchQuery($adapterName, $identifier)
    {
        return new FetchPaymentQuery($adapterName, $identifier);
    }
}
