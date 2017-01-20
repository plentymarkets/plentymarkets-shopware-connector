<?php

namespace PlentyConnector\Connector\TransferObject\PaymentStatus;

use PlentyConnector\Connector\TransferObject\TransferObjectInterface;

/**
 * Interface PaymentStatusInterface
 */
interface PaymentStatusInterface extends TransferObjectInterface
{
    /**
     * @return string
     */
    public function getName();
}
