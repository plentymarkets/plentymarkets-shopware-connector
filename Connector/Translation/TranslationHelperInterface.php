<?php

namespace PlentyConnector\Connector\Translation;

use PlentyConnector\Connector\TransferObject\TranslateableInterface;

/**
 * Interface TranslationHelperInterface
 */
interface TranslationHelperInterface
{
    /**
     * @param string $languageIdentifier
     * @param TranslateableInterface $object
     *
     * @return TransferObjectInterface
     */
    public function translate($languageIdentifier, TranslateableInterface $object);
}
