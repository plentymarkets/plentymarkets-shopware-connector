<?php

namespace PlentymarketsAdapter\ResponseParser\Manufacturer;

use PlentyConnector\Connector\TransferObject\Manufacturer\ManufacturerInterface;

/**
 * Interface ManufacturerResponseParserInterface
 */
interface ManufacturerResponseParserInterface
{
    /**
     * @param array $entry
     *
     * @return ManufacturerInterface|null
     */
    public function parse(array $entry);
}
