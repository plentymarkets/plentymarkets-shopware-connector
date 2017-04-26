<?php

namespace PlentyConnector\Components\Bundle\TransferObject\BundleProduct;

use PlentyConnector\Connector\ValueObject\AbstractValueObject;

/**
 * Class BundleProduct
 */
class BundleProduct extends AbstractValueObject
{
    /**
     * @var string
     */
    private $number;

    /**
     * @var float
     */
    private $amount;

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
}
