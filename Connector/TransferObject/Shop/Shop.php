<?php

namespace PlentyConnector\Connector\TransferObject\Shop;

use PlentyConnector\Connector\TransferObject\AbstractTransferObject;

/**
 * Class Shop
 */
class Shop extends AbstractTransferObject
{
    const TYPE = 'Shop';

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
        return $this->identifier;
    }

    /**
     * @param string $identifier
     */
    public function setIdentifier($identifier)
    {
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
        $this->name = $name;
    }
}
