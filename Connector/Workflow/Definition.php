<?php

namespace PlentyConnector\Connector\Workflow;

use Assert\Assertion;

/**
 * Class Definition
 *
 * @package PlentyConnector\Connector\Workflow
 */
class Definition implements DefinitionInterface
{
    /**
     * origin adapter name
     *
     * @var string
     */
    private $originAdapterName;

    /**
     * destination adapter name
     *
     * @var string
     */
    private $destinationAdapterName;

    /**
     * The TransferObject class name
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
