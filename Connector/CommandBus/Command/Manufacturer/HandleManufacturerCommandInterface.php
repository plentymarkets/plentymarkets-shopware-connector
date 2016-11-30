<?php

namespace PlentyConnector\Connector\CommandBus\Command\Manufacturer;

use PlentyConnector\Connector\CommandBus\Command\CommandInterface;
use PlentyConnector\Connector\TransferObject\Manufacturer\ManufacturerInterface;

/**
 * Interface HandleManufacturerCommandInterface
 */
interface HandleManufacturerCommandInterface extends CommandInterface
{
    /**
     * @return ManufacturerInterface
     */
    public function getManufacturer();

    /**
     * @return string
     */
    public function getAdapterName();
}
