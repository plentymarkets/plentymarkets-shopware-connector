<?php

namespace PlentyConnector\Connector\ServiceBus\Query\Stock;

use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;

/**
 * Class GetChangedStockQuery.
 */
class GetChangedStockQuery implements QueryInterface
{
    /**
     * @var string
     */
    private $adapterName;

    /**
     * GetChangedStockQuery constructor.
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
