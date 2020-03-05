<?php

namespace SystemConnector\TransferObject\Order;

use DateTimeImmutable;
use ReflectionClass;
use SystemConnector\TransferObject\AbstractTransferObject;
use SystemConnector\TransferObject\AttributableInterface;
use SystemConnector\TransferObject\Order\Address\Address;
use SystemConnector\TransferObject\Order\Comment\Comment;
use SystemConnector\TransferObject\Order\Customer\Customer;
use SystemConnector\TransferObject\Order\OrderItem\OrderItem;
use SystemConnector\TransferObject\Order\Package\Package;
use SystemConnector\ValueObject\Attribute\Attribute;

class Order extends AbstractTransferObject implements AttributableInterface
{
    const TYPE = 'Order';

    /**
     * Identifier of the object.
     *
     * @var string
     */
    private $identifier = '';

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
     * @var Package[]
     */
    private $packages = [];

    /**
     * @var Attribute[]
     */
    private $attributes = [];

    public function __construct()
    {
        $this->orderTime = new DateTimeImmutable('now');
    }

    /**
     * {@inheritdoc}
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

    public function getOrderTypes(): array
    {
        $reflection = new ReflectionClass(__CLASS__);

        return $reflection->getConstants();
    }

    public function getOrderNumber(): string
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

    public function getOrderTime(): DateTimeImmutable
    {
        return $this->orderTime;
    }

    public function setOrderTime(DateTimeImmutable $orderTime)
    {
        $this->orderTime = $orderTime;
    }

    public function getCustomer(): Customer
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

    public function getBillingAddress(): Address
    {
        return $this->billingAddress;
    }

    public function setBillingAddress(Address $billingAddress)
    {
        $this->billingAddress = $billingAddress;
    }

    public function getShippingAddress(): Address
    {
        return $this->shippingAddress;
    }

    public function setShippingAddress(Address $shippingAddress)
    {
        $this->shippingAddress = $shippingAddress;
    }

    /**
     * @return OrderItem[]
     */
    public function getOrderItems(): array
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

    public function getOrderStatusIdentifier(): string
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

    public function getPaymentStatusIdentifier(): string
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

    public function getShippingProfileIdentifier(): string
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
    public function getComments(): array
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
     * @return Package[]
     */
    public function getPackages(): array
    {
        return $this->packages;
    }

    /**
     * @param Package[] $packages
     */
    public function setPackages($packages)
    {
        $this->packages = $packages;
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
            'orderNumber' => $this->getOrderNumber(),
            'orderTime' => $this->getOrderTime(),
            'customer' => $this->getCustomer(),
            'billingAddress' => $this->getBillingAddress(),
            'shoppingAddress' => $this->getShippingAddress(),
            'orderItems' => $this->getOrderItems(),
            'shopIdentifier' => $this->getShopIdentifier(),
            'currencyIdentifier' => $this->getCurrencyIdentifier(),
            'orderStatusIdentifier' => $this->getOrderStatusIdentifier(),
            'paymentStatusIdentifier' => $this->getPaymentStatusIdentifier(),
            'paymentMethodIdentifier' => $this->getPaymentMethodIdentifier(),
            'shippingProfileIdentifier' => $this->getShippingProfileIdentifier(),
            'comments' => $this->getComments(),
            'packages' => $this->getPackages(),
            'attributes' => $this->getAttributes(),
        ];
    }
}
