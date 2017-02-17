<?php

namespace PlentyConnector\Connector\TransferObject\PaymentStatus;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\AbstractTransferObject;

/**
 * Class PaymentStatus
 */
class PaymentStatus extends AbstractTransferObject
{
    const TYPE = 'PaymentStatus';

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
