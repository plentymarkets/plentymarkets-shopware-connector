<?php

namespace PlentyConnector\Connector\QueryBus\Query\Unit;

use Assert\Assertion;
use PlentyConnector\Connector\QueryBus\Query\FetchQueryInterface;

/**
 * Class FetchUnitQuery
 */
class FetchUnitQuery implements FetchQueryInterface
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
     * FetchUnitQuery constructor.
     *
     * @param string $adapterName
     * @param string $identifier
     */
    public function __construct($adapterName, $identifier)
    {
        Assertion::string($adapterName);
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
