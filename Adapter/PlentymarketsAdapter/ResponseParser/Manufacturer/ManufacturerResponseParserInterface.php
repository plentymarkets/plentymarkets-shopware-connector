<?php

namespace PlentymarketsAdapter\ResponseParser\Manufacturer;

use SystemConnector\TransferObject\TransferObjectInterface;

interface ManufacturerResponseParserInterface
{
    /**
     * @param array $entry
     *
     * @return TransferObjectInterface[]
     */
    public function parse(array $entry);
}
