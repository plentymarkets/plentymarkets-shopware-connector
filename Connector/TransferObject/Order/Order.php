<?php

namespace PlentyConnector\Connector\TransferObject\Order;

use Assert\Assertion;

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
     * @param $orderStatusId
     * @param $paymentStatusId
     * @param $paymentMethodId
     * @param $shippingProfileId
     * @param $shopId
     */
    public function __construct($identifier, $orderNumber, $orderStatusId,
        $paymentStatusId, $paymentMethodId, $shippingProfileId, $shopId)
    {
        Assertion::uuid($identifier);
        Assertion::string($orderNumber);
        Assertion::string($orderStatusId);
        Assertion::string($paymentStatusId);
        Assertion::string($paymentMethodId);
        Assertion::string($shippingProfileId);
        Assertion::string($shopId);

        $this->identifier = $identifier;
        $this->orderNumber = $orderNumber;
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
