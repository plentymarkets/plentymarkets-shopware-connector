<?php

namespace PlentyConnector\Connector\TransferObject\Order;

use PlentyConnector\Connector\TransferObject\Dispatch;
use PlentyConnector\Connector\TransferObject\Payment;
use PlentyConnector\Connector\TransferObject\Shop;
use PlentyConnector\Connector\TransferObject\TransferObjectInterface;

/**
 * Interface OrderInterface.
 */
interface OrderInterface extends TransferObjectInterface
{
    /**
     * @return string
     */
    public function getIdentifier();

    /**
     * @return string
     */
    public function getOrderNumber();

    /**
     * @return string
     */
    public function getOrderStatusId();

    /**
     * @return string
     */
    public function getPaymentStatusId();

    /**
     * @return string
     */
    public function getPaymentMethodId();

    /**
     * @return string
     */
    public function getShippingProfileId();

    /**
     * @return string
     */
    public function getShopId();
}
