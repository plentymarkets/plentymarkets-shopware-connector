<?php

namespace ShopwareAdapter\ResponseParser\Language;

use PlentyConnector\Connector\TransferObject\Language\Language;

interface LanguageResponseParserInterface
{
    /**
     * @param array $entry
     *
     * @return null|Language
     */
    public function parse(array $entry);
}
