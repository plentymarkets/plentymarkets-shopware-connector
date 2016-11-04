<?php

namespace PlentyConnector\Connector\TransferObject\Mapping;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\MappedTransferObjectInterface;
use PlentyConnector\Connector\TransferObject\TransferObjectType;

/**
 * Class Mapping
 */
class Mapping implements MappingInterface
{
    /**
     * origin adapter name.
     *
     * @var string
     */
    private $originAdapterName;

    /**
     * @var MappedTransferObjectInterface[]
     */
    private $originTransferObjects;

    /**
     * destination adapter name.
     *
     * @var string
     */
    private $destinationAdapterName;

    /**
     * @var MappedTransferObjectInterface[]
     */
    private $destinationTransferObjects;

    /**
     * @var bool
     */
    private $isComplete;

    /**
     * Mapping constructor.
     *
     * @param string $originAdapterName
     * @param MappedTransferObjectInterface[] $originTransferObjects
     * @param string $destinationAdapterName
     * @param MappedTransferObjectInterface[] $destinationTransferObjects
     * @param $isComplete bool
     */
    public function __construct(
        $originAdapterName,
        array $originTransferObjects,
        $destinationAdapterName,
        array $destinationTransferObjects,
        $isComplete
    ) {
        Assertion::string($originAdapterName);
        Assertion::allIsInstanceOf($originTransferObjects, MappedTransferObjectInterface::class);

        Assertion::string($destinationAdapterName);
        Assertion::allIsInstanceOf($destinationTransferObjects, MappedTransferObjectInterface::class);

        $this->originAdapterName = $originAdapterName;
        $this->originTransferObjects = $originTransferObjects;
        $this->destinationAdapterName = $destinationAdapterName;
        $this->destinationTransferObjects = $destinationTransferObjects;
        $this->isComplete = $isComplete;
    }

    /**
     * @return string
     */
    public static function getType()
    {
        return TransferObjectType::MAPPING;
    }

    /**
     * {@inheritdoc}
     */
    public static function fromArray(array $params = [])
    {
        Assertion::allInArray(array_keys($params), [
            'originAdapterName',
            'originTransferObjects',
            'destinationAdapterName',
            'destinationTransferObjects',
            'isComplete'
        ]);

        return new self(
            $params['originAdapterName'],
            $params['originTransferObjects'],
            $params['destinationAdapterName'],
            $params['destinationTransferObjects'],
            $params['isComplete']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getOriginAdapterName()
    {
        return $this->originAdapterName;
    }

    /**
     * {@inheritdoc}
     */
    public function getOriginTransferObjects()
    {
        return $this->originTransferObjects;
    }

    /**
     * {@inheritdoc}
     */
    public function getDestinationAdapterName()
    {
        return $this->destinationAdapterName;
    }

    /**
     * {@inheritdoc}
     */
    public function getDestinationTransferObjects()
    {
        return $this->destinationTransferObjects;
    }

    /**
     * {@inheritdoc}
     */
    public function isIsComplete()
    {
        return $this->isComplete;
    }
}
