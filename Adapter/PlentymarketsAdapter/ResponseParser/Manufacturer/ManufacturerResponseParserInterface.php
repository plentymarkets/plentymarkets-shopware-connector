<?php

namespace PlentymarketsAdapter\ResponseParser\Manufacturer;

use SystemConnector\TransferObject\TransferObjectInterface;

interface ManufacturerResponseParserInterface
{
    /**
     * @return TransferObjectInterface[]
     */
    public function parse(array $entry): array;
}
