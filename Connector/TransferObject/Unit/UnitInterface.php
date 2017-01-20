<?php

namespace PlentyConnector\Connector\TransferObject\Unit;

use PlentyConnector\Connector\TransferObject\TransferObjectInterface;

/**
 * Interface PaymentStatusInterface
 */
interface UnitInterface extends TransferObjectInterface
{
    /**
     * @return string
     */
    public function getName();
}
