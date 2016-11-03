<?php

namespace PlentyConnector\Connector\TransferObject\ShippingProfile;

/**
 * Interface ShippingProfileInterface.
 */
interface ShippingProfileInterface extends TransferObjectInterface
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
