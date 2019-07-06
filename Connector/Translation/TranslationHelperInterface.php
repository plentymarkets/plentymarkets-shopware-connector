<?php

namespace SystemConnector\Translation;

use SystemConnector\TransferObject\TranslatableInterface;

interface TranslationHelperInterface
{
    /**
     * @param TranslatableInterface $object
     *
     * @return array
     */
    public function getLanguageIdentifiers(TranslatableInterface $object): array;

    /**
     * @param string                $languageIdentifier
     * @param TranslatableInterface $object
     *
     * @return TranslatableInterface
     */
    public function translate($languageIdentifier, TranslatableInterface $object): TranslatableInterface;
}
