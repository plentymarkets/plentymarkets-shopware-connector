<?php

namespace PlentyConnector\Connector\TransferObject\Definition;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\TransferObjectType;

/**
 * Class Definition.
 */
class Definition implements DefinitionInterface
{
    /**
     * origin adapter name.
     *
     * @var string
     */
    private $originAdapterName;

    /**
     * destination adapter name.
     *
     * @var string
     */
    private $destinationAdapterName;

    /**
     * The TransferObject class name.
     *
     * @var string
     */
    private $objectType;

    /**
     * Definition constructor.
     *
     * @param string $originAdapterName
     * @param string $destinationAdapterName
     * @param string $objectType
     */
    public function __construct($originAdapterName, $destinationAdapterName, $objectType)
    {
        Assertion::string($originAdapterName);
        Assertion::string($destinationAdapterName);
        Assertion::string($objectType);

        $this->originAdapterName = $originAdapterName;
        $this->destinationAdapterName = $destinationAdapterName;
        $this->objectType = $objectType;
    }

    /**
     * {@inheritdoc}
     */
    public static function getType()
    {
        return TransferObjectType::DEFINITION;
    }

    /**
     * {@inheritdoc}
     */
    public static function fromArray(array $params = [])
    {
        Assertion::allInArray(array_keys($params), [
            'originAdapterName',
            'destinationAdapterName',
            'objectType',
        ]);

        return new self(
            $params['originAdapterName'],
            $params['destinationAdapterName'],
            $params['objectType']
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
    public function getDestinationAdapterName()
    {
        return $this->destinationAdapterName;
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectType()
    {
        return $this->objectType;
    }
}
