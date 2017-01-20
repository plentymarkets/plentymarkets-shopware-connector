<?php

namespace PlentyConnector\Connector\ServiceBus\Query\Order;

use Assert\Assertion;
use PlentyConnector\Connector\ServiceBus\Query\FetchChangedQueryInterface;

/**
 * Class FetchChangedOrdersQuery.
 */
class FetchChangedOrdersQuery implements FetchChangedQueryInterface
{
    /**
     * @var string
     */
    private $adapterName;

    /**
     * FetchChangedOrdersQuery constructor.
     *
     * @param string $adapterName
     */
    public function __construct($adapterName)
    {
        Assertion::string($adapterName);

        $this->adapterName = $adapterName;
    }

    /**
     * @return string
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
