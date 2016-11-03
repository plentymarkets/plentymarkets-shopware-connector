<?php

namespace PlentymarketsAdapter\ResponseParser;

use PlentyConnector\Connector\TransferObject\Manufacturer\ManufacturerInterface;

/**
 * Interface ResponseParserInterface
 */
interface ResponseParserInterface
{
    /**
     * @param $entry
     *
     * @return ManufacturerInterface
     */
    public function parseManufacturer($entry);
}
