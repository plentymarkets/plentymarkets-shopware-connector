<?php

namespace PlentyConnector\Connector\TransferObject\PaymentMethod;

/**
 * Interface PaymentMethodInterface
 *
 * @package PlentyConnector\Connector\TransferObject\PaymentMethod
 */
interface PaymentMethodInterface extends TransferObjectInterface
{
    /**
     * @return string
     */
    public function getIdentifier();

    /**
     * @return string
     */
    public function getName();
}
