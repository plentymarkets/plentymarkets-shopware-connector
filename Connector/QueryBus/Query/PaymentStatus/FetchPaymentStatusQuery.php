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
     * FetchPaymentStatusQuery constructor.
     *
     * @param string $adapterName
     * @param $identifier
     */
    public function __construct($adapterName, $identifier)
    {
        Assertion::uuid($identifier);

        $this->adapterName = $adapterName;
        $this->identifier = $identifier;
    }

    /**
     * @return string
     */
    public function getAdapterName()
    {
        return $this->adapterName;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @return array
     */
    public function getPayload()
    {
        return [
            'adapterName' => $this->adapterName,
            'identifier' => $this->identifier,
        ];
    }

    /**
     * @param array $payload
     */
    public function setPayload(array $payload = [])
    {
        $this->adapterName = $payload['adapterName'];
        $this->identifier = $payload['identifier'];
    }
}
