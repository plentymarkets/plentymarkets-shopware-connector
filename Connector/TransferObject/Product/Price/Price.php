<?php

namespace SystemConnector\TransferObject\Product\Price;

use SystemConnector\TransferObject\AbstractTransferObject;

class Price extends AbstractTransferObject
{
    const TYPE = 'Price';

    /**
     * Identifier of the object.
     *
     * @var string
     */
    private $identifier = '';

    /**
     * @var string
     */
    private $variationIdentifier = '';

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
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return self::TYPE;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * {@inheritdoc}
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * @return string
     */
    public function getVariationIdentifier(): string
    {
        return $this->variationIdentifier;
    }

    /**
     * @param string $variationIdentifier
     */
    public function setVariationIdentifier($variationIdentifier)
    {
        $this->variationIdentifier = $variationIdentifier;
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
     * @return float
     */
    public function getPseudoPrice(): float
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
    public function getFromAmount(): float
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
     * @return null|float
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

    /**
     * {@inheritdoc}
     */
    public function getClassProperties()
    {
        return [
            'price' => $this->getPrice(),
            'pseudoPrice' => $this->getPseudoPrice(),
            'customerGroupIdentifier' => $this->getCustomerGroupIdentifier(),
            'fromAmount' => $this->getFromAmount(),
            'toAmount' => $this->getToAmount(),
        ];
    }
}
