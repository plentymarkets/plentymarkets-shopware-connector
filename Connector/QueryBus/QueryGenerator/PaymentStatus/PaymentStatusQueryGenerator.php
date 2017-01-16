<?php

namespace PlentyConnector\Connector\QueryBus\QueryGenerator\PaymentStatus;

use PlentyConnector\Connector\QueryBus\Query\PaymentStatus\FetchAllPaymentStatusesQuery;
use PlentyConnector\Connector\QueryBus\Query\PaymentStatus\FetchChangedPaymentStatusesQuery;
use PlentyConnector\Connector\QueryBus\Query\PaymentStatus\FetchPaymentStatusQuery;
use PlentyConnector\Connector\QueryBus\QueryGenerator\QueryGeneratorInterface;
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
    public function generateFetchChangedQuery($adapterName)
    {
        return new FetchChangedPaymentStatusesQuery($adapterName);
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
    public function generateFetchQuery($adapterName, $identifier)
    {
        return new FetchPaymentStatusQuery($adapterName, $identifier);
    }
}
