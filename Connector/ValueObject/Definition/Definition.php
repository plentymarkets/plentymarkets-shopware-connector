<?php

namespace PlentyConnector\Connector\ValueObject\Definition;

use Assert\Assertion;
use PlentyConnector\Connector\ValueObject\AbstractValueObject;

/**
 * Class Definition.
 */
class Definition extends AbstractValueObject
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
     * @var int
     */
    private $priority = 0;

    /**
     * @return string
     */
    public function getOriginAdapterName()
    {
        return $this->originAdapterName;
    }

    /**
     * @param string $originAdapterName
     */
    public function setOriginAdapterName($originAdapterName)
    {
        Assertion::string($originAdapterName);
        Assertion::notBlank($originAdapterName);

        $this->originAdapterName = $originAdapterName;
    }

    /**
     * @return string
     */
    public function getDestinationAdapterName()
    {
        return $this->destinationAdapterName;
    }

    /**
     * @param string $destinationAdapterName
     */
    public function setDestinationAdapterName($destinationAdapterName)
    {
        Assertion::string($destinationAdapterName);
        Assertion::notBlank($destinationAdapterName);

        $this->destinationAdapterName = $destinationAdapterName;
    }

    /**
     * @return string
     */
    public function getObjectType()
    {
        return $this->objectType;
    }

    /**
     * @param string $objectType
     */
    public function setObjectType($objectType)
    {
        Assertion::string($objectType);
        Assertion::notBlank($objectType);

        $this->objectType = $objectType;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param null|int $priority
     */
    public function setPriority($priority)
    {
        if (null === $priority) {
            $priority = 0;
        }

        Assertion::integer($priority);

        $this->priority = $priority;
    }
}
