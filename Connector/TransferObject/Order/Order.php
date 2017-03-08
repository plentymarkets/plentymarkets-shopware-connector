<?php

namespace PlentyConnector\Connector\TransferObject\Order;

use Assert\Assertion;
use DateTimeImmutable;
use PlentyConnector\Connector\TransferObject\AbstractTransferObject;
use PlentyConnector\Connector\TransferObject\Order\Address\Address;
use PlentyConnector\Connector\TransferObject\Order\Comment\Comment;
use PlentyConnector\Connector\TransferObject\Order\Customer\Customer;
use PlentyConnector\Connector\TransferObject\Order\OrderItem\OrderItem;
use PlentyConnector\Connector\TransferObject\Order\Payment\Payment;
use PlentyConnector\Connector\ValueObject\Attribute\Attribute;

/**
 * Class Order.
 */
class Order extends AbstractTransferObject
{
    const TYPE = 'Order';

    const TYPE_ORDER = 1;
    const TYPE_OFFER = 2;

    /**
     * Identifier of the object.
     *
     * @var string
     */
    private $identifier = '';

    /**
     * @var int
     */
    private $orderType = self::TYPE_ORDER;

    /**
     * @var string
     */
    private $orderNumber = '';

    /**
     * @var DateTimeImmutable;
     */
    private $orderTime;

    /**
     * @var null|Customer
     */
    private $customer;

    /**
     * @var null|Address
     */
    private $billingAddress;

    /**
     * @var null|Address
     */
    private $shippingAddress;

    /**
     * @var OrderItem[]
     */
    private $orderItems = [];

    /**
     * @var Payment[]
     */
    private $payments = [];

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
    private $orderStatusIdentifier = '';

    /**
     * @var string
     */
    private $paymentStatusIdentifier = '';

    /**
     * @var string
     */
    private $paymentMethodIdentifier = '';

    /**
     * @var string
     */
    private $shippingProfileIdentifier = '';

    /**
     * @var Comment[]
     */
    private $comments = [];

    /**
     * @var Attribute[]
     */
    private $attributes = [];

    /**
     * Order constructor.
     */
    public function __construct()
    {
        $timezone = new \DateTimeZone('UTC');
        $this->orderTime = new DateTimeImmutable('now', $timezone);
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        Assertion::notBlank($this->identifier);

        return $this->identifier;
    }

    /**
     * @param string $identifier
     */
    public function setIdentifier($identifier)
    {
        Assertion::uuid($identifier);

        $this->identifier = $identifier;
    }

    /**
     * @return int
     */
    public function getOrderType()
    {
        return $this->orderType;
    }

    /**
     * @param int $orderType
     */
    public function setOrderType($orderType)
    {
        $this->orderType = $orderType;
    }

    /**
     * @return array
     */
    public function getOrderTypes()
    {
        $reflection = new \ReflectionClass(__CLASS__);

        return $reflection->getConstants();
    }

    /**
     * @return string
     */
    public function getOrderNumber()
    {
        return $this->orderNumber;
    }

    /**
     * @param string $orderNumber
     */
    public function setOrderNumber($orderNumber)
    {
        $this->orderNumber = $orderNumber;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getOrderTime()
    {
        return $this->orderTime;
    }

    /**
     * @param DateTimeImmutable $orderTime
     */
    public function setOrderTime($orderTime)
    {
        $this->orderTime = $orderTime;
    }

    /**
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param Customer $customer
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;
    }

    /**
     * @return Address
     */
    public function getBillingAddress()
    {
        return $this->billingAddress;
    }

    /**
     * @param Address $billingAddress
     */
    public function setBillingAddress(Address $billingAddress)
    {
        $this->billingAddress = $billingAddress;
    }

    /**
     * @return Address
     */
    public function getShippingAddress()
    {
        return $this->shippingAddress;
    }

    /**
     * @param Address $shippingAddress
     */
    public function setShippingAddress(Address $shippingAddress)
    {
        $this->shippingAddress = $shippingAddress;
    }

    /**
     * @return OrderItem[]
     */
    public function getOrderItems()
    {
        return $this->orderItems;
    }

    /**
     * @param OrderItem[] $orderItems
     */
    public function setOrderItems(array $orderItems)
    {
        $this->orderItems = $orderItems;
    }

    /**
     * @return Payment[]
     */
    public function getPayments()
    {
        return $this->payments;
    }

    /**
     * @param Payment[] $payments
     */
    public function setPayments(array $payments)
    {
        $this->payments = $payments;
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
    public function getOrderStatusIdentifier()
    {
        return $this->orderStatusIdentifier;
    }

    /**
     * @param string $orderStatusIdentifier
     */
    public function setOrderStatusIdentifier($orderStatusIdentifier)
    {
        $this->orderStatusIdentifier = $orderStatusIdentifier;
    }

    /**
     * @return string
     */
    public function getPaymentStatusIdentifier()
    {
        return $this->paymentStatusIdentifier;
    }

    /**
     * @param string $paymentStatusIdentifier
     */
    public function setPaymentStatusIdentifier($paymentStatusIdentifier)
    {
        $this->paymentStatusIdentifier = $paymentStatusIdentifier;
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
    public function getShippingProfileIdentifier()
    {
        return $this->shippingProfileIdentifier;
    }

    /**
     * @param string $shippingProfileIdentifier
     */
    public function setShippingProfileIdentifier($shippingProfileIdentifier)
    {
        $this->shippingProfileIdentifier = $shippingProfileIdentifier;
    }

    /**
     * @return Comment[]
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @param Comment[] $comments
     */
    public function setComments($comments)
    {
        $this->comments = $comments;
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
