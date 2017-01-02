<?php

namespace PlentyConnector\Connector\TransferObject\Manufacturer;

use PlentyConnector\Connector\TransferObject\NameableInterface;
use PlentyConnector\Connector\TransferObject\SynchronizedTransferObjectInterface;

/**
 * Interface ManufacturerInterface.
 */
interface ManufacturerInterface extends SynchronizedTransferObjectInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getLogo();

    /**
     * @return string
     */
    public function getLink();
}
