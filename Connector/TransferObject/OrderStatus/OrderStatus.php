<?php

namespace PlentyConnector\Connector\TransferObject\OrderStatus;

use Assert\Assertion;

/**
 * Class OrderStatus
 */
class OrderStatus implements OrderStatusInterface
{
    const TYPE = 'OrderStatus';

    /**
     * @var string
     */
    private $identifier;

    /**
     * @var string
     */
    private $name;

    /**
     * OrderStatus constructor.
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
    public function getType()
    {
        return self::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public static function fromArray(array $params = [])
    {
        Assertion::allInArray(array_keys($params), [
            'identifier',
            'name',
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
