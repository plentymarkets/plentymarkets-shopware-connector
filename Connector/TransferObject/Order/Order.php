<?php

namespace PlentyConnector\Connector\TransferObject\Order;

use PlentyConnector\Connector\TransferObject\Dispatch;
use PlentyConnector\Connector\TransferObject\Payment;
use PlentyConnector\Connector\TransferObject\Shop;

/**
 * Class Order
 *
 * @package PlentyConnector\Connector\TransferObject
 */
class Order implements OrderInterface
{
    /**
     * @var string
     */
    private $orderNumber;

    /**
     * @var OrderStatus
     */
    private $orderStatus;

    /**
     * @var PaymentStatus
     */
    private $paymentStatus;

    /**
     * @var Payment
     */
    private $payment;

    /**
     * @var Dispatch
     */
    private $dispatch;

    /**
     * @var Shop
     */
    private $shop;

    /**
     * Stock constructor.
     *
     * @param Shop $shop
     */
    public function __construct($orderNumber, $orderStatus,
                                $paymentStatus, $payment, $dispatch, $shop)
    {
        // TODO assert

        $this->orderNumber = $orderNumber;
        $this->orderStatus = $orderStatus;
        $this->paymentStatus = $paymentStatus;
        $this->payment = $payment;
        $this->dispatch = $dispatch;
        $this->shop = $shop;
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
            $params['orderNumber'],
            $params['orderStatus'],
            $params['paymentStatus'],
            $params['payment'],
            $params['dispatch'],
            $params['shop']
        );
    }

    /**
     * @inheritdoc
     */
    public function getOrderNumber()
    {
        return $this->orderNumber;
    }

    /**
     * @inheritdoc
     */
    public function getOrderStatus()
    {
        return $this->orderStatus;
    }

    /**
     * @inheritdoc
     */
    public function getPaymentStatus()
    {
        return $this->paymentStatus;
    }

    /**
     * @inheritdoc
     */
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * @inheritdoc
     */
    public function getDispatch()
    {
        return $this->dispatch;
    }

    /**
     * @inheritdoc
     */
    public function getShop()
    {
        return $this->shop;
    }
}
