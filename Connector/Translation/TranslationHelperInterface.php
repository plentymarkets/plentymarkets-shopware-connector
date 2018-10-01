<?php

namespace PlentyConnector\Connector\Translation;

use PlentyConnector\Connector\TransferObject\TranslateableInterface;

interface TranslationHelperInterface
{
    /**
     * @param TranslateableInterface $object
     *
     * @return array
     */
    public function getLanguageIdentifiers(TranslateableInterface $object);

    /**
     * @param string                 $languageIdentifier
     * @param TranslateableInterface $object
     *
     * @return TranslateableInterface
     */
    public function translate($languageIdentifier, TranslateableInterface $object);
}
