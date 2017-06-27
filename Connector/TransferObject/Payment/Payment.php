<?php

namespace PlentyConnector\Connector\TransferObject\Payment;

use PlentyConnector\Connector\TransferObject\AbstractTransferObject;
use PlentyConnector\Connector\TransferObject\AttributableInterface;
use PlentyConnector\Connector\TransferObject\Payment\PaymentData\PaymentDataInterface;
use PlentyConnector\Connector\ValueObject\Attribute\Attribute;

/**
 * Class Payment
 */
class Payment extends AbstractTransferObject implements AttributableInterface
{
    const TYPE = 'Payment';

    /**
     * @var string
     */
    private $identifier;

    /**
     * @var string
     */
    private $orderIdentifer;

    /**
     * @var float
     */
    private $amount = 0.0;

    /**
     * @var string
     */
    private $shopIdentifier = '';

    /**
     * @var string
     */
    private $currencyIdentifier = '';

    /**
     * @var string
     */
    private $paymentMethodIdentifier = '';

    /**
     * @var string
     */
    private $transactionReference = '';

    /**
     * @var string
     */
    private $accountHolder = '';

    /**
     * @var null|PaymentDataInterface
     */
    private $paymentData;

    /**
     * @var Attribute[]
     */
    private $attributes = [];

    /**
     * return the unique type of the object.
     *
     * @return string
     */
    public function getType()
    {
        return self::TYPE;
    }

    /**
     * return a uuid.
     *
     * @return string
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
    public function getOrderIdentifer()
    {
        return $this->orderIdentifer;
    }

    /**
     * @param string $orderIdentifer
     */
    public function setOrderIdentifer($orderIdentifer)
    {
        $this->orderIdentifer = $orderIdentifer;
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
     * @return string
     */
    public function getShopIdentifier()
    {
        return $this->shopIdentifier;
    }

    /**
     * @param string $shopIdentifier
     */
    public function setShopIdentifier($shopIdentifier)
    {
        $this->shopIdentifier = $shopIdentifier;
    }

    /**
     * @return string
     */
    public function getCurrencyIdentifier()
    {
        return $this->currencyIdentifier;
    }

    /**
     * @param string $currencyIdentifier
     */
    public function setCurrencyIdentifier($currencyIdentifier)
    {
        $this->currencyIdentifier = $currencyIdentifier;
    }

    /**
     * @return string
     */
    public function getPaymentMethodIdentifier()
    {
        return $this->paymentMethodIdentifier;
    }

    /**
     * @param string $paymentMethodIdentifier
     */
    public function setPaymentMethodIdentifier($paymentMethodIdentifier)
    {
        $this->paymentMethodIdentifier = $paymentMethodIdentifier;
    }

    /**
     * @return string
     */
    public function getTransactionReference()
    {
        return $this->transactionReference;
    }

    /**
     * @param string $transactionReference
     */
    public function setTransactionReference($transactionReference)
    {
        $this->transactionReference = $transactionReference;
    }

    /**
     * @return string
     */
    public function getAccountHolder()
    {
        return $this->accountHolder;
    }

    /**
     * @param string $accountHolder
     */
    public function setAccountHolder($accountHolder = '')
    {
        $this->accountHolder = $accountHolder;
    }

    /**
     * @return null|PaymentDataInterface
     */
    public function getPaymentData()
    {
        return $this->paymentData;
    }

    /**
     * @param null|PaymentDataInterface $paymentData
     */
    public function setPaymentData(PaymentDataInterface $paymentData = null)
    {
        $this->paymentData = $paymentData;
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
