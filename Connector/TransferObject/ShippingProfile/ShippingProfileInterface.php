<?php

namespace PlentyConnector\Connector\TransferObject\ShippingProfile;

use PlentyConnector\Connector\TransferObject\TransferObjectInterface;

/**
 * Interface ShippingProfileInterface.
 */
interface ShippingProfileInterface extends TransferObjectInterface
{
    /**
     * @return string
     */
    public function getName();
}
