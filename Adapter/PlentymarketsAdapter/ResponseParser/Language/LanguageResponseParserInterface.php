<?php

namespace PlentymarketsAdapter\ResponseParser\Language;

use SystemConnector\TransferObject\Language\Language;

interface LanguageResponseParserInterface
{
    /**
     * @param array $entry
     *
     * @return null|Language
     */
    public function parse(array $entry);
}
