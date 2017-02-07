<?php

namespace PlentyConnector\Connector\TransferObject\Manufacturer;

use PlentyConnector\Connector\TransferObject\TransferObjectInterface;

/**
 * Interface ManufacturerInterface.
 */
interface ManufacturerInterface extends TransferObjectInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getLogoIdentifier();

    /**
     * @return string
     */
    public function getLink();
}
