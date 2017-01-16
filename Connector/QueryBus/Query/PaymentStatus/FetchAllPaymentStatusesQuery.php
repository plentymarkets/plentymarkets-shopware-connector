<?php

namespace PlentyConnector\Connector\QueryBus\Query\PaymentStatus;

use Assert\Assertion;
use PlentyConnector\Connector\QueryBus\Query\FetchAllQueryInterface;

/**
 * Class FetchAllPaymentStatusesQuery.
 */
class FetchAllPaymentStatusesQuery implements FetchAllQueryInterface
{
    /**
     * @var string
     */
    private $adapterName;

    /**
     * FetchAllPaymentStatussQuery constructor.
     *
     * @param string $adapterName
     */
    public function __construct($adapterName)
    {
        Assertion::string($adapterName);

        $this->adapterName = $adapterName;
    }

    /**
     * {@inheritdoc}
     */
    public function getAdapterName()
    {
        return $this->adapterName;
    }

    /**
     * {@inheritdoc}
     */
    public function getPayload()
    {
        return [
            'adapterName' => $this->adapterName,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function setPayload(array $payload = [])
    {
        $this->adapterName = $payload['adapterName'];
    }
}
