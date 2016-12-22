<?php

namespace PlentyConnector\Connector\TransferObject\Unit;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\TransferObjectType;

/**
 * Class Unit
 */
class Unit implements UnitInterface
{
    /**
     * @var string
     */
    private $identifier;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $unit;

    /**
     * Unit constructor.
     *
     * @param string $identifier
     * @param string $name
     * @param string $unit
     */
    public function __construct($identifier, $name, $unit)
    {
        Assertion::uuid($identifier);
        Assertion::string($name);

        $this->identifier = $identifier;
        $this->unit = $unit;
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public static function getType()
    {
        return TransferObjectType::UNIT;
    }

    /**
     * {@inheritdoc}
     */
    public static function fromArray(array $params = [])
    {
        Assertion::allInArray(array_keys($params), [
            'identifier',
            'name',
            'unit'
        ]);

        return new self(
            $params['identifier'],
            $params['name'],
            $params['unit']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getUnit()
    {
        return $this->unit;
    }
}
