<?php

namespace PlentyConnector\Connector\ValueObject\Definition;

use Assert\Assertion;

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
     * @var int
     */
    private $priority;

    /**
     * Definition constructor.
     *
     * @param string $originAdapterName
     * @param string $destinationAdapterName
     * @param string $objectType
     * @param int|null $priority
     */
    public function __construct($originAdapterName, $destinationAdapterName, $objectType, $priority = null)
    {
        Assertion::string($originAdapterName);
        Assertion::notBlank($originAdapterName);
        Assertion::string($destinationAdapterName);
        Assertion::notBlank($destinationAdapterName);
        Assertion::string($objectType);
        Assertion::notBlank($objectType);
        Assertion::nullOrInteger($priority);

        $this->originAdapterName = $originAdapterName;
        $this->destinationAdapterName = $destinationAdapterName;
        $this->objectType = $objectType;
        $this->priority = $priority;
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

        if (!array_key_exists('priority', $params)) {
            $params['priority'] = 0;
        }

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

    /**
     * {@inheritdoc}
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->objectType . ': ' . $this->originAdapterName . ' > ' . $this->destinationAdapterName;
    }
}
