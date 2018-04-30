<?php

namespace PlentyConnector\Connector\TransferObject\Product\LinkedProduct;

use PlentyConnector\Connector\ValueObject\AbstractValueObject;
use ReflectionClass;

/**
 * Class LinkedProduct
 */
class LinkedProduct extends AbstractValueObject
{
    const TYPE_ACCESSORY = 'accessory';
    const TYPE_REPLACEMENT = 'replacement';
    const TYPE_SIMILAR = 'similar';

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
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
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
        $reflection = new ReflectionClass(__CLASS__);

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
