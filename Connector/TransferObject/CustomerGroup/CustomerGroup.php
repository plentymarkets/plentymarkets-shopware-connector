<?php

namespace PlentyConnector\Connector\TransferObject\CustomerGroup;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\TransferObjectType;

/**
 * Class CustomerGroup
 */
class CustomerGroup implements CustomerGroupInterface
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
     * CustomerGroup constructor.
     *
     * @param string $identifier
     * @param string $name
     */
    public function __construct($identifier, $name)
    {
        Assertion::uuid($identifier);
        Assertion::string($name);

        $this->identifier = $identifier;
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public static function getType()
    {
        return TransferObjectType::CUSTOMER_GROUP;
    }

    /**
     * {@inheritdoc}
     */
    public static function fromArray(array $params = [])
    {
        Assertion::allInArray(array_keys($params), [
            'identifier',
            'name'
        ]);

        return new self(
            $params['identifier'],
            $params['name']
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
}
