<?php

namespace PlentyConnector\Connector\TransferObject\Definition;
//namespace PlentyConnector\Connector\TransferObject\Definition;

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
     * @return string
     */
    public static function getType()
    {
        return TransferObjectType::DEFINITION;
    }

    /**
     * @param array $params
     *
     * @return self
     */
    public static function fromArray(array $params = [])
    {
        return new self(
            $params['originAdapterName'],
            $params['destinationAdapterName'],
            $params['objectType']
        );
    }

    /**
     * @return string
     */
    public function getOriginAdapterName()
    {
        return $this->originAdapterName;
    }

    /**
     * @return string
     */
    public function getDestinationAdapterName()
    {
        return $this->destinationAdapterName;
    }

    /**
     * @return string
     */
    public function getObjectType()
    {
        return $this->objectType;
    }
}
