<?php

namespace PlentymarketsAdapter\ResponseParser\MediaCategory;

use SystemConnector\TransferObject\MediaCategory\MediaCategory;

interface MediaCategoryResponseParserInterface
{
    /**
     * @return null|MediaCategory
     */
    public function parse(array $entry);
}
