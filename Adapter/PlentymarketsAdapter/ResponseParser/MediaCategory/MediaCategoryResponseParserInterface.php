<?php

namespace PlentymarketsAdapter\ResponseParser\MediaCategory;

use PlentyConnector\Connector\TransferObject\MediaCategory\MediaCategory;

/**
 * Interface MediaCategoryResponseParserInterface.
 */
interface MediaCategoryResponseParserInterface
{
    /**
     * @param array $entry
     *
     * @return null|MediaCategory
     */
    public function parse(array $entry);
}
