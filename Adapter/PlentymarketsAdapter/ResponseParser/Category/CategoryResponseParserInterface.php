<?php

namespace PlentymarketsAdapter\ResponseParser\Category;

use SystemConnector\TransferObject\TransferObjectInterface;

interface CategoryResponseParserInterface
{
    /**
     * @return TransferObjectInterface[]
     */
    public function parse(array $entry): array;
}
