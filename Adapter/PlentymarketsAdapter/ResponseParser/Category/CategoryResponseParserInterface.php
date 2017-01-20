<?php

namespace PlentymarketsAdapter\ResponseParser\Category;

use PlentyConnector\Connector\TransferObject\Category\CategoryInterface;

/**
 * Interface CategoryResponseParserInterface
 */
interface CategoryResponseParserInterface
{
    /**
     * @param array $entry
     *
     * @return CategoryInterface[]
     */
    public function parse(array $entry);
}
