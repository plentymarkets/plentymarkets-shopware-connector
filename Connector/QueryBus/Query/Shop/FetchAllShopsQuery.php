<?php

namespace PlentyConnector\Connector\QueryBus\Query\Shop;

use PlentyConnector\Connector\QueryBus\Query\FetchAllQueryInterface;

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
     * FetchAllManufacturersQuery constructor.
     *
     * @param string $adapterName
     */
    public function __construct($adapterName)
    {
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
     * @return array
     */
    public function getPayload()
    {
        return [
            'adapterName' => $this->adapterName,
        ];
    }

    /**
     * @param array $payload
     */
    public function setPayload(array $payload = [])
    {
        $this->adapterName = $payload['adapterName'];
    }
}
