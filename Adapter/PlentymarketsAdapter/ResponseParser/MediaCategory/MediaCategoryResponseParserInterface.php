<?php

namespace PlentymarketsAdapter\ResponseParser\MediaCategory;

use PlentyConnector\Connector\TransferObject\MediaCategory\MediaCategoryInterface;

/**
 * Interface MediaCategoryResponseParserInterface
 */
interface MediaCategoryResponseParserInterface
{
    /**
     * @param array $entry
     *
     * @return MediaCategoryInterface|null
     */
    public function parse(array $entry);
}
