<?php

namespace PlentyConnector\Connector\TransferObject\Order;

use PlentyConnector\Connector\TransferObject\Dispatch;
use PlentyConnector\Connector\TransferObject\Payment;
use PlentyConnector\Connector\TransferObject\Shop;
use PlentyConnector\Connector\TransferObject\TransferObjectInterface;

/**
 * Interface OrderInterface
 *
 * @package PlentyConnector\Connector\TransferObject
 */
interface OrderInterface extends TransferObjectInterface
{

    /**
     * @return string
     */
    public function getOrderNumber();

    /**
     * @return OrderStatus
     */
    public function getOrderStatus();

    /**
     * @return PaymentStatus
     */
    public function getPaymentStatus();

    /**
     * @return Payment
     */
    public function getPayment();

    /**
     * @return Dispatch
     */
    public function getDispatch();

    /**
     * @return Shop
     */
    public function getShop();
}
