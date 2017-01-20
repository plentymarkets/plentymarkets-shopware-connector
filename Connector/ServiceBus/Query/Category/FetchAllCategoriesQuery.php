<?php

namespace PlentyConnector\Connector\ServiceBus\Query\Category;

use Assert\Assertion;
use PlentyConnector\Connector\ServiceBus\Query\FetchAllQueryInterface;

/**
 * Class FetchAllCategoriesQuery.
 */
class FetchAllCategoriesQuery implements FetchAllQueryInterface
{
    /**
     * @var string
     */
    private $adapterName;

    /**
     * FetchAllCategoriesQuery constructor.
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
