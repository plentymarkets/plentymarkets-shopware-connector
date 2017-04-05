<?php

namespace PlentyConnector\Connector\Translation;

use PlentyConnector\Connector\TransferObject\TransferObjectInterface;
use PlentyConnector\Connector\TransferObject\TranslateableInterface;

/**
 * Interface TranslationHelperInterface
 */
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
     * @return TransferObjectInterface
     */
    public function translate($languageIdentifier, TranslateableInterface $object);
}
