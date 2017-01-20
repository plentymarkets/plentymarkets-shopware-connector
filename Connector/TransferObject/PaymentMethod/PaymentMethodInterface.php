<?php

namespace PlentyConnector\Connector\TransferObject\PaymentMethod;

use PlentyConnector\Connector\TransferObject\TransferObjectInterface;

/**
 * Interface PaymentMethodInterface.
 */
interface PaymentMethodInterface extends TransferObjectInterface
{
    /**
     * @return string
     */
    public function getName();
}
