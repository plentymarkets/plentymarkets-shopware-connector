<?php

namespace PlentymarketsAdapter\ResponseParser\MediaCategory;

use SystemConnector\TransferObject\MediaCategory\MediaCategory;

interface MediaCategoryResponseParserInterface
{
    /**
     * @param array $entry
     *
     * @return null|MediaCategory
     */
    public function parse(array $entry);
}
