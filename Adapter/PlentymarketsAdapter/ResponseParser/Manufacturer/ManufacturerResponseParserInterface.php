<?php

namespace PlentymarketsAdapter\ResponseParser\Manufacturer;

use PlentyConnector\Connector\TransferObject\TransferObjectInterface;

interface ManufacturerResponseParserInterface
{
    /**
     * @param array $entry
     *
     * @return TransferObjectInterface[]
     */
    public function parse(array $entry);
}
