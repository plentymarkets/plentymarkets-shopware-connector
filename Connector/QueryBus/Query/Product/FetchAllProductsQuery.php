<?php

namespace PlentyConnector\Connector\QueryBus\Query\Product;

use Assert\Assertion;
use PlentyConnector\Connector\QueryBus\Query\QueryInterface;

/**
 * Class FetchAllProductsQuery.
 */
class FetchAllProductsQuery implements QueryInterface
{
    /**
     * @var string
     */
    private $adapterName;

    /**
     * FetchAllProductsQuery constructor.
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
