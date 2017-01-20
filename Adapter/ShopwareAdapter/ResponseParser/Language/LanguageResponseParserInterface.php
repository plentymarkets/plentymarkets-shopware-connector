<?php

namespace ShopwareAdapter\ResponseParser\Language;

use PlentyConnector\Connector\TransferObject\Language\LanguageInterface;

/**
 * Interface LanguageResponseParserInterface
 */
interface LanguageResponseParserInterface
{
    /**
     * @param array $entry
     *
     * @return LanguageInterface|null
     */
    public function parse(array $entry);
}
