<?php

namespace PlentyConnector\Components\Bundle\TransferObject\BundleProduct;

use SystemConnector\ValueObject\AbstractValueObject;
use SystemConnector\ValueObject\Attribute\Attribute;

class BundleProduct extends AbstractValueObject
{
    /**
     * @var string
     */
    private $number;

    /**
     * @var float
     */
    private $amount = 0.0;

    /**
     * @var int
     */
    private $position = 0;

    /**
     * @var Attribute[]
     */
    private $attributes = [];

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
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
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
     * @return Attribute[]
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param Attribute[] $attributes
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function getClassProperties()
    {
        return [
            'number' => $this->getNumber(),
            'amount' => $this->getAmount(),
            'position' => $this->getPosition(),
            'attributes' => $this->getAttributes(),
        ];
    }
}
