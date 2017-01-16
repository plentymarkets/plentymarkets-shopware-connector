<?php

namespace PlentyConnector\Connector\QueryBus\Query\PaymentStatus;

use Assert\Assertion;
use PlentyConnector\Connector\QueryBus\Query\FetchQueryInterface;

/**
 * Class FetchPaymentStatusQuery
 */
class FetchPaymentStatusQuery implements FetchQueryInterface
{
    /**
     * @var string
     */
    private $adapterName;

    /**
     * @var string
     */
    private $identifier;

    /**
     * FetchManufacturerQuery constructor.
     *
     * @param string $identifier
     * @param string $adapterName
     */
    public function __construct($identifier, $adapterName)
    {
        Assertion::uuid($identifier);
        Assertion::string($adapterName);

        $this->identifier = $identifier;
        $this->adapterName = $adapterName;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return $this->identifier;
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
            'identifier' => $this->identifier,
            'adapterName' => $this->adapterName,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function setPayload(array $payload = [])
    {
        $this->identifier = $payload['identifier'];
        $this->adapterName = $payload['adapterName'];
    }
}
