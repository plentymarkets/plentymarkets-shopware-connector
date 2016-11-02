<?php

namespace PlentyConnector\Connector\TransferObject\ShippingProfile;

/**
 * Interface ShippingProfileInterface
 *
 * @package PlentyConnector\Connector\TransferObject\ShippingProfile
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
