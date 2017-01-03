<?php

namespace PlentyConnector\Connector\TransferObject\Country;

use PlentyConnector\Connector\TransferObject\MappedTransferObjectInterface;

/**
 * Interface CountryInterface
 */
interface CountryInterface extends MappedTransferObjectInterface
{
    /**
     * @return string
     */
    public function getCountryCode();
}
