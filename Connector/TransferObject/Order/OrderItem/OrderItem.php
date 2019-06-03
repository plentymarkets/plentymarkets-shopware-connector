<?php

namespace SystemConnector\TransferObject\Order\OrderItem;

use ReflectionClass;
use SystemConnector\TransferObject\AttributableInterface;
use SystemConnector\ValueObject\AbstractValueObject;
use SystemConnector\ValueObject\Attribute\Attribute;

class OrderItem extends AbstractValueObject implements AttributableInterface
{
    const TYPE_PRODUCT = 'product';
    const TYPE_VOUCHER = 'voucher';
    const TYPE_COUPON = 'coupon';
    const TYPE_DISCOUNT = 'discount';
    const TYPE_PAYMENT_SURCHARGE = 'payment_surcharge';
    const TYPE_SHIPPING_COSTS = 'shipping_costs';

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
     * @var null|string
     */
    private $vatRateIdentifier;

    /**
     * @var Attribute[]
     */
    private $attributes = [];

    /**
     * @return string
     */
    public function getType(): string
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
    public function getTypes(): array
    {
        $reflection = new ReflectionClass(__CLASS__);

        return $reflection->getConstants();
    }

    /**
     * @return float
     */
    public function getQuantity(): float
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
    public function getName(): string
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
    public function getNumber(): string
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
    public function getPrice(): float
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
     * @return null|string
     */
    public function getVatRateIdentifier()
    {
        return $this->vatRateIdentifier;
    }

    /**
     * @param null|string $vatRateIdentifier
     */
    public function setVatRateIdentifier($vatRateIdentifier = null)
    {
        $this->vatRateIdentifier = $vatRateIdentifier;
    }

    /**
     * @return Attribute[]
     */
    public function getAttributes(): array
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

    /**
     * {@inheritdoc}
     */
    public function getClassProperties()
    {
        return [
            'type' => $this->getType(),
            'quantity' => $this->getQuantity(),
            'name' => $this->getName(),
            'number' => $this->getNumber(),
            'price' => $this->getPrice(),
            'vatRateIdentifier' => $this->getVatRateIdentifier(),
            'attributes' => $this->getAttributes(),
        ];
    }
}
