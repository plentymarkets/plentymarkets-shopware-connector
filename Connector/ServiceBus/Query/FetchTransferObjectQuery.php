<?php

namespace PlentyConnector\Connector\ServiceBus\Query;

use Assert\Assertion;
use PlentyConnector\Connector\ServiceBus\QueryType;

/**
 * Class FetchTransferObjectQuery
 */
class FetchTransferObjectQuery implements QueryInterface
{
    /**
     * @var string
     */
    private $adapterName;

    /**
     * @var string
     */
    private $objectType;

    /**
     * @var string
     */
    private $queryType;

    /**
     * @var null|string
     */
    private $objectIdentifier;

    /**
     * FetchTransferObjectQuery constructor.
     *
     * @param string $adapterName
     * @param string $objectType
     * @param string $queryType
     * @param null   $objectIdentifier
     */
    public function __construct($adapterName, $objectType, $queryType, $objectIdentifier = null)
    {
        Assertion::string($adapterName);
        Assertion::string($objectType);
        Assertion::inArray($queryType, QueryType::getAllTypes());

        if ($queryType === QueryType::ONE) {
            Assertion::notBlank($objectIdentifier);
            Assertion::uuid($objectIdentifier);
        }

        $this->objectType = $objectType;
        $this->adapterName = $adapterName;
        $this->queryType = $queryType;
        $this->objectIdentifier = $objectIdentifier;
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
    public function getObjectType()
    {
        return $this->objectType;
    }

    /**
     * @return string
     */
    public function getQueryType()
    {
        return $this->queryType;
    }

    /**
     * @return null|string
     */
    public function getObjectIdentifier()
    {
        return $this->objectIdentifier;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'adapterName' => $this->adapterName,
            'objectType' => $this->objectType,
            'queryType' => $this->queryType,
            'objectIdentifier' => $this->objectIdentifier,
        ];
    }
}
