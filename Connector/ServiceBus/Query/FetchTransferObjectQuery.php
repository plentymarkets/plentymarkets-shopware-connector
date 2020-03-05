<?php

namespace SystemConnector\ServiceBus\Query;

use Assert\Assertion;
use SystemConnector\ServiceBus\QueryType;

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

    public function getAdapterName(): string
    {
        return $this->adapterName;
    }

    public function getObjectType(): string
    {
        return $this->objectType;
    }

    public function getQueryType(): string
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

    public function toArray(): array
    {
        return [
            'adapterName' => $this->adapterName,
            'objectType' => $this->objectType,
            'queryType' => $this->queryType,
            'objectIdentifier' => $this->objectIdentifier,
        ];
    }
}
