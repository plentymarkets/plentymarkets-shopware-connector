<?php

namespace PlentyConnector\Connector\TransferObject\Country;

use PlentyConnector\Connector\TransferObject\TransferObjectInterface;

/**
 * Interface CountryInterface
 */
interface CountryInterface extends TransferObjectInterface
{
    /**
     * @return string
     */
    public function getName();
}
