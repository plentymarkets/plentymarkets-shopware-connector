<?php

namespace SystemConnector\Translation;

use SystemConnector\TransferObject\TranslatableInterface;

interface TranslationHelperInterface
{
    public function getLanguageIdentifiers(TranslatableInterface $object): array;

    /**
     * @param string $languageIdentifier
     */
    public function translate($languageIdentifier, TranslatableInterface $object): TranslatableInterface;
}
