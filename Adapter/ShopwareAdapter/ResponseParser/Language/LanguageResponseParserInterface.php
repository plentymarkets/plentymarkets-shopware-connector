<?php

namespace ShopwareAdapter\ResponseParser\Language;

use SystemConnector\TransferObject\Language\Language;

interface LanguageResponseParserInterface
{
    /**
     * @return null|Language
     */
    public function parse(array $entry);
}
