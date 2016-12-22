<?php

namespace PlentyConnector\Connector\QueryBus\Query\ShippingProfile;

use Assert\Assertion;
use PlentyConnector\Connector\QueryBus\Query\FetchAllQueryInterface;

/**
 * Class FetchAllShippingProfilesQuery.
 */
class FetchAllShippingProfilesQuery implements FetchAllQueryInterface
{
    /**
     * @var string
     */
    private $adapterName;

    /**
     * FetchAllShippingProfilesQuery constructor.
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
