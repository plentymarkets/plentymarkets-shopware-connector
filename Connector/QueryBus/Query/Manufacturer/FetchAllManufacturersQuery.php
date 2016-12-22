<?php

namespace PlentyConnector\Connector\QueryBus\Query\Manufacturer;

use Assert\Assertion;
use PlentyConnector\Connector\QueryBus\Query\FetchAllQueryInterface;

/**
 * Class FetchAllManufacturersQuery.
 */
class FetchAllManufacturersQuery implements FetchAllQueryInterface
{
    /**
     * @var string
     */
    private $adapterName;

    /**
     * FetchAllManufacturersQuery constructor.
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
