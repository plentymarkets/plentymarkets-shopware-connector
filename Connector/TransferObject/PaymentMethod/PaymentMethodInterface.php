<?php

namespace PlentyConnector\Connector\TransferObject\PaymentMethod;

/**
 * Interface PaymentMethodInterface.
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
