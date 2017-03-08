<?php

namespace PlentyConnector\Connector\TransferObject\Product\Price;

use PlentyConnector\Connector\ValueObject\AbstractValueObject;

/**
 * Class Price.
 */
class Price extends AbstractValueObject
{
    /**
     * @var float
     */
    private $price = 0.0;

    /**
     * @var float
     */
    private $pseudoPrice = 0.0;

    /**
     * @var null|string
     */
    private $customerGroupIdentifier;

    /**
     * @var float
     */
    private $fromAmount = 1.0;

    /**
     * @var null|float
     */
    private $toAmount;

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
     * @return float
     */
    public function getPseudoPrice()
    {
        return $this->pseudoPrice;
    }

    /**
     * @param float $pseudoPrice
     */
    public function setPseudoPrice($pseudoPrice)
    {
        $this->pseudoPrice = $pseudoPrice;
    }

    /**
     * @return null|string
     */
    public function getCustomerGroupIdentifier()
    {
        return $this->customerGroupIdentifier;
    }

    /**
     * @param null|string $customerGroupIdentifier
     */
    public function setCustomerGroupIdentifier($customerGroupIdentifier = null)
    {
        $this->customerGroupIdentifier = $customerGroupIdentifier;
    }

    /**
     * @return float
     */
    public function getFromAmount()
    {
        return $this->fromAmount;
    }

    /**
     * @param float $fromAmount
     */
    public function setFromAmount($fromAmount)
    {
        $this->fromAmount = $fromAmount;
    }

    /**
     * @return float
     */
    public function getToAmount()
    {
        return $this->toAmount;
    }

    /**
     * @param null|float $toAmount
     */
    public function setToAmount($toAmount = null)
    {
        $this->toAmount = $toAmount;
    }
}
