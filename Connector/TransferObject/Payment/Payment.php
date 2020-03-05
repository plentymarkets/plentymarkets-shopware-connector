<?php

namespace SystemConnector\TransferObject\Payment;

use SystemConnector\TransferObject\AbstractTransferObject;
use SystemConnector\TransferObject\AttributableInterface;
use SystemConnector\TransferObject\Payment\PaymentData\PaymentDataInterface;
use SystemConnector\ValueObject\Attribute\Attribute;

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
    private $orderIdentifier;

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
     */
    public function getType(): string
    {
        return self::TYPE;
    }

    /**
     * {@inheritdoc}
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

    public function getOrderIdentifier(): string
    {
        return $this->orderIdentifier;
    }

    /**
     * @param string $orderIdentifier
     */
    public function setOrderIdentifier($orderIdentifier)
    {
        $this->orderIdentifier = $orderIdentifier;
    }

    public function getAmount(): float
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

    public function getShopIdentifier(): string
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

    public function getCurrencyIdentifier(): string
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

    public function getPaymentMethodIdentifier(): string
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

    public function getTransactionReference(): string
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

    public function getAccountHolder(): string
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

    public function setPaymentData(PaymentDataInterface $paymentData = null)
    {
        $this->paymentData = $paymentData;
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
            'identifier' => $this->getIdentifier(),
            'orderIdentifier' => $this->getOrderIdentifier(),
            'amount' => $this->getAmount(),
            'shopIdentifier' => $this->getShopIdentifier(),
            'currencyIdentifier' => $this->getCurrencyIdentifier(),
            'paymentMethodIdentifier' => $this->getPaymentMethodIdentifier(),
            'transactionReference' => $this->getTransactionReference(),
            'accountHolder' => $this->getAccountHolder(),
            'paymentData' => $this->getPaymentData(),
            'attributes' => $this->getAttributes(),
        ];
    }
}
