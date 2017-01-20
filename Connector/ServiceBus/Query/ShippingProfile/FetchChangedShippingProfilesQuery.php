<?php

namespace PlentyConnector\Connector\ServiceBus\Query\ShippingProfile;

use Assert\Assertion;
use PlentyConnector\Connector\ServiceBus\Query\FetchChangedQueryInterface;

/**
 * Class FetchChangedShippingProfilesQuery.
 */
class FetchChangedShippingProfilesQuery implements FetchChangedQueryInterface
{
    /**
     * @var string
     */
    private $adapterName;

    /**
     * FetchChangedShippingProfilesQuery constructor.
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
