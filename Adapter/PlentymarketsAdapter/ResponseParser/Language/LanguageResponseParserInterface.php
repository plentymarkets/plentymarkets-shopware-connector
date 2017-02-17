<?php

namespace PlentymarketsAdapter\ResponseParser\Language;

use PlentyConnector\Connector\TransferObject\Language\Language;

/**
 * Interface LanguageResponseParserInterface
 */
interface LanguageResponseParserInterface
{
    /**
     * @param array $entry
     *
     * @return null|Language
     */
    public function parse(array $entry);
}
