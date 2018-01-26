<?php

namespace PlentyConnector\Connector\TransferObject\Product\LinkedProduct;

use PlentyConnector\Connector\ValueObject\AbstractValueObject;

/**
 * Class LinkedProduct
 */
class LinkedProduct extends AbstractValueObject
{
    const TYPE_ACCESSORY = 1;
    const TYPE_REPLACEMENT = 2;
    const TYPE_SIMILAR = 3;

    /**
     * @var string
     */
    private $type = self::TYPE_ACCESSORY;

    /**
     * @var int
     */
    private $position = 0;

    /**
     * @var string
     */
    private $productIdentifier = '';

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param int $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return array
     */
    public function getTypes()
    {
        $reflection = new \ReflectionClass(__CLASS__);

        return $reflection->getConstants();
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param int $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return string
     */
    public function getProductIdentifier()
    {
        return $this->productIdentifier;
    }

    /**
     * @param string $productIdentifier
     */
    public function setProductIdentifier($productIdentifier)
    {
        $this->productIdentifier = $productIdentifier;
    }
}
