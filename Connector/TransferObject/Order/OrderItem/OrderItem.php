<?php

namespace PlentyConnector\Connector\TransferObject\Order\OrderItem;

use PlentyConnector\Connector\ValueObject\AbstractValueObject;
use PlentyConnector\Connector\ValueObject\Attribute\Attribute;

/**
 * Class OrderItem
 */
class OrderItem extends AbstractValueObject
{
    const TYPE_PRODUCT = 1;
    const TYPE_VOUCHER = 2;
    const TYPE_COUPON = 3;
    const TYPE_DISCOUNT = 4;
    const TYPE_PAYMENT_SURCHARGE = 5;
    const TYPE_SHIPPING_COSTS = 6;

    /**
     * @var int
     */
    private $type = self::TYPE_PRODUCT;

    /**
     * @var float
     */
    private $quantity = 1.0;

    /**
     * @var string
     */
    private $name = '';

    /**
     * @var string
     */
    private $number = '';

    /**
     * @var float
     */
    private $price = 0.0;

    /**
     * @var string
     */
    private $vatRateIdentifier = '';

    /**
     * @var Attribute[]
     */
    private $attributes = [];

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
     * @return float
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param float $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
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

    /**
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param string $number
     */
    public function setNumber($number)
    {
        $this->number = $number;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param float $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @return string
     */
    public function getVatRateIdentifier()
    {
        return $this->vatRateIdentifier;
    }

    /**
     * @param string $vatRateIdentifier
     */
    public function setVatRateIdentifier($vatRateIdentifier)
    {
        $this->vatRateIdentifier = $vatRateIdentifier;
    }

    /**
     * @return Attribute[]
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param Attribute[] $attributes
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
    }
}
