<?php

namespace PlentyConnector\Connector\ServiceBus\QueryGenerator\PaymentStatus;

use PlentyConnector\Connector\ServiceBus\Query\PaymentStatus\FetchAllPaymentStatusesQuery;
use PlentyConnector\Connector\ServiceBus\Query\PaymentStatus\FetchChangedPaymentStatusesQuery;
use PlentyConnector\Connector\ServiceBus\Query\PaymentStatus\FetchPaymentStatusQuery;
use PlentyConnector\Connector\ServiceBus\QueryGenerator\QueryGeneratorInterface;
use PlentyConnector\Connector\TransferObject\PaymentStatus\PaymentStatus;

/**
 * Class PaymentStatusQueryGenerator
 */
class PaymentStatusQueryGenerator implements QueryGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($transferObjectType)
    {
        return $transferObjectType === PaymentStatus::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchAllQuery($adapterName)
    {
        return new FetchAllPaymentStatusesQuery($adapterName);
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchChangedQuery($adapterName)
    {
        return new FetchChangedPaymentStatusesQuery($adapterName);
    }

    /**
     * {@inheritdoc}
     */
    public function generateFetchQuery($adapterName, $identifier)
    {
        return new FetchPaymentStatusQuery($adapterName, $identifier);
    }
}
