<?php

namespace PlentyConnector\Connector\QueryBus\Query\Stock;

use PlentyConnector\Connector\QueryBus\Query\QueryInterface;

/**
 * Class GetStockQuery.
 */
class GetStockQuery implements QueryInterface
{
    /**
     * @var string
     */
    private $adapterName;

    /**
     * GetStockQuery constructor.
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
