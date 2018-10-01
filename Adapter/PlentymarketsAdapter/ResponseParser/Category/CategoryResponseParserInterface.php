<?php

namespace PlentymarketsAdapter\ResponseParser\Category;

use PlentyConnector\Connector\TransferObject\TransferObjectInterface;

interface CategoryResponseParserInterface
{
    /**
     * @param array $entry
     *
     * @return TransferObjectInterface[]
     */
    public function parse(array $entry);
}
