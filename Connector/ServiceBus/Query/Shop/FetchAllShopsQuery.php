<?php

namespace PlentyConnector\Connector\ServiceBus\Query\Shop;

use Assert\Assertion;
use PlentyConnector\Connector\ServiceBus\Query\FetchAllQueryInterface;

/**
 * Class FetchAllShopsQuery.
 */
class FetchAllShopsQuery implements FetchAllQueryInterface
{
    /**
     * @var string
     */
    private $adapterName;

    /**
     * FetchAllShopsQuery constructor.
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
