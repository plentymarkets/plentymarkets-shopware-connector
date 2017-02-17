<?php

namespace PlentyConnector\Connector\TransferObject\OrderStatus;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\AbstractTransferObject;

/**
 * Class OrderStatus
 */
class OrderStatus extends AbstractTransferObject
{
    const TYPE = 'OrderStatus';

    /**
     * @var string
     */
    private $identifier = '';

    /**
     * @var string
     */
    private $name = '';

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
    public function getIdentifier()
    {
        Assertion::notBlank($this->identifier);

        return $this->identifier;
    }

    /**
     * @param string $identifier
     */
    public function setIdentifier($identifier)
    {
        Assertion::uuid($identifier);

        $this->identifier = $identifier;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        Assertion::string($name);
        Assertion::notBlank($name);

        $this->name = $name;
    }
}
