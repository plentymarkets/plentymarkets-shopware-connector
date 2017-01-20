<?php

namespace PlentyConnector\Connector\TransferObject\CustomerGroup;

use PlentyConnector\Connector\TransferObject\TransferObjectInterface;

/**
 * Interface CustomerGroupInterface
 */
interface CustomerGroupInterface extends TransferObjectInterface
{
    /**
     * @return string
     */
    public function getName();
}
