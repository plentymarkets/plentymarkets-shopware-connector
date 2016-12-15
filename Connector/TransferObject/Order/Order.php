<?php

namespace PlentyConnector\Connector\TransferObject\Order;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\OrderItem\OrderItemInterface;

/**
 * Class Order.
 */
class Order implements OrderInterface
{
    /**
     * Identifier of the object.
     *
     * @var string
     */
    private $identifier;

    /**
     * @var string
     */
    private $orderNumber;

    /**
     * @var OrderItemInterface[]
     */
    private $orderItems;

    /**
     * @var string
     */
    private $orderStatusId;

    /**
     * @var string
     */
    private $paymentStatusId;

    /**
     * @var string
     */
    private $paymentMethodId;

    /**
     * @var string
     */
    private $shippingProfileId;

    /**
     * @var string
     */
    private $shopId;

    /**
     * Order constructor.
     *
     * @param $identifier
     * @param $orderNumber
     * @param $orderItems
     * @param $orderStatusId
     * @param $paymentStatusId
     * @param $paymentMethodId
     * @param $shippingProfileId
     * @param $shopId
     */
    public function __construct(
        $identifier,
        $orderNumber,
        $orderItems,
        $orderStatusId,
        $paymentStatusId,
        $paymentMethodId,
        $shippingProfileId,
        $shopId
    ) {
        Assertion::uuid($identifier);
        Assertion::string($orderNumber);
        Assertion::isArray($orderItems);
        Assertion::string($orderStatusId);
        Assertion::string($paymentStatusId);
        Assertion::string($paymentMethodId);
        Assertion::string($shippingProfileId);
        Assertion::string($shopId);

        $this->identifier = $identifier;
        $this->orderNumber = $orderNumber;
        $this->orderItems = $orderItems;
        $this->orderStatusId = $orderStatusId;
        $this->paymentStatusId = $paymentStatusId;
        $this->paymentMethodId = $paymentMethodId;
        $this->shippingProfileId = $shippingProfileId;
        $this->shopId = $shopId;
    }

    /**
     * @return string
     */
    public static function getType()
    {
        return 'Order';
    }

    /**
     * @param array $params
     *
     * @return self
     */
    public static function fromArray(array $params = [])
    {
        return new self(
            $params['identifier'],
            $params['orderNumber'],
            $params['orderItems'],
            $params['orderStatusId'],
            $params['paymentStatusId'],
            $params['paymentMethodId'],
            $params['shippingProfileId'],
            $params['shopId']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderNumber()
    {
        return $this->orderNumber;
    }

    /**
     * @return OrderItemInterface[]
     */
    public function getOrderItems()
    {
        return $this->orderItems;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderStatusId()
    {
        return $this->orderStatusId;
    }

    /**
     * {@inheritdoc}
     */
    public function getPaymentStatusId()
    {
        return $this->paymentStatusId;
    }

    /**
     * {@inheritdoc}
     */
    public function getPaymentMethodId()
    {
        return $this->paymentMethodId;
    }

    /**
     * {@inheritdoc}
     */
    public function getShippingProfileId()
    {
        return $this->shippingProfileId;
    }

    /**
     * {@inheritdoc}
     */
    public function getShopId()
    {
        return $this->shopId;
    }
}
