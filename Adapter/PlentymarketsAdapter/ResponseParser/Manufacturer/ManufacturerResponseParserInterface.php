<?php

namespace PlentymarketsAdapter\ResponseParser\Manufacturer;

use PlentyConnector\Connector\TransferObject\Manufacturer\Manufacturer;

/**
 * Interface ManufacturerResponseParserInterface.
 */
interface ManufacturerResponseParserInterface
{
    /**
     * @param array $entry
     *
     * @return null|Manufacturer
     */
    public function parse(array $entry);
}
