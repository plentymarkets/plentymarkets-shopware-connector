<?php

namespace PlentyConnector\Connector\TransferObject\Product\Price;

use Assert\Assertion;
use PlentyConnector\Connector\ValueObject\AbstractValueObject;

/**
 * Class Price.
 */
class Price  extends AbstractValueObject
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
    private $currencyIdentifier;

    /**
     * @var null|string
     */
    private $customerGroupIdentifier;

    /**
     * @var int
     */
    private $fromAmount = 1;

    /**
     * @var null|int
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
        Assertion::float($price);
        Assertion::greaterOrEqualThan($price, '0.0');

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
        Assertion::float($pseudoPrice);
        Assertion::greaterOrEqualThan($pseudoPrice, '0.0');

        $this->pseudoPrice = $pseudoPrice;
    }

    /**
     * @return null|string
     */
    public function getCurrencyIdentifier()
    {
        return $this->currencyIdentifier;
    }

    /**
     * @param null|string $currencyIdentifier
     */
    public function setCurrencyIdentifier($currencyIdentifier = null)
    {
        Assertion::nullOrUuid($currencyIdentifier);

        $this->currencyIdentifier = $currencyIdentifier;
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
        Assertion::nullOrUuid($customerGroupIdentifier);

        $this->customerGroupIdentifier = $customerGroupIdentifier;
    }

    /**
     * @return int
     */
    public function getFromAmount()
    {
        return $this->fromAmount;
    }

    /**
     * @param int $fromAmount
     */
    public function setFromAmount($fromAmount)
    {
        Assertion::integer($fromAmount);
        Assertion::greaterOrEqualThan($fromAmount, 1);

        $this->fromAmount = $fromAmount;
    }

    /**
     * @return int
     */
    public function getToAmount()
    {
        return $this->toAmount;
    }

    /**
     * @param null|int $toAmount
     */
    public function setToAmount($toAmount = null)
    {
        Assertion::nullOrInteger($toAmount);

        $this->toAmount = $toAmount;
    }


}
