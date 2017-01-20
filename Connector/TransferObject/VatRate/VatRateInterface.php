<?php

namespace PlentyConnector\Connector\TransferObject\VatRate;

use PlentyConnector\Connector\TransferObject\TransferObjectInterface;

/**
 * Interface VatRateInterface
 */
interface VatRateInterface extends TransferObjectInterface
{
    /**
     * @return string
     */
    public function getName();
}
