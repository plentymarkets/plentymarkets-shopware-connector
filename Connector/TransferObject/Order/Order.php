<?php

namespace PlentyConnector\Connector\TransferObject\Order;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\AbstractTransferObject;
use PlentyConnector\Connector\TransferObject\OrderItem\OrderItem;

/**
 * Class Order.
 */
class Order extends AbstractTransferObject
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
     * @var OrderItem[]
     */
    private $orderItems = [];

    /**
     * @var string
     */
    private $orderStatusId = '';

    /**
     * @var string
     */
    private $paymentStatusId = '';

    /**
     * @var string
     */
    private $paymentMethodId = '';

    /**
     * @var string
     */
    private $shippingProfileId = '';

    /**
     * @var string
     */
    private $shopId = '';

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
        Assertion::string($orderNumber);

        $this->orderNumber = $orderNumber;
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
    public function setOrderItems($orderItems)
    {
        Assertion::allIsInstanceOf($orderItems, OrderItem::class);

        $this->orderItems = $orderItems;
    }

    /**
     * @return string
     */
    public function getOrderStatusId()
    {
        return $this->orderStatusId;
    }

    /**
     * @param string $orderStatusId
     */
    public function setOrderStatusId($orderStatusId)
    {
        Assertion::string($orderStatusId);

        $this->orderStatusId = $orderStatusId;
    }

    /**
     * @return string
     */
    public function getPaymentStatusId()
    {
        return $this->paymentStatusId;
    }

    /**
     * @param string $paymentStatusId
     */
    public function setPaymentStatusId($paymentStatusId)
    {
        Assertion::string($paymentStatusId);

        $this->paymentStatusId = $paymentStatusId;
    }

    /**
     * @return string
     */
    public function getPaymentMethodId()
    {
        return $this->paymentMethodId;
    }

    /**
     * @param string $paymentMethodId
     */
    public function setPaymentMethodId($paymentMethodId)
    {
        Assertion::string($paymentMethodId);

        $this->paymentMethodId = $paymentMethodId;
    }

    /**
     * @return string
     */
    public function getShippingProfileId()
    {
        return $this->shippingProfileId;
    }

    /**
     * @param string $shippingProfileId
     */
    public function setShippingProfileId($shippingProfileId)
    {
        Assertion::string($shippingProfileId);

        $this->shippingProfileId = $shippingProfileId;
    }

    /**
     * @return string
     */
    public function getShopId()
    {
        return $this->shopId;
    }

    /**
     * @param string $shopId
     */
    public function setShopId($shopId)
    {
        Assertion::string($shopId);

        $this->shopId = $shopId;
    }
}
