<?php

namespace PlentyConnector\Connector\TransferObject;

use PlentyConnector\Connector\TransferObject\Translation\TranslationInterface;

/**
 * Interface TranslateableInterface
 */
interface TranslateableInterface
{
    /**
     * @param TranslationInterface $translation
     */
    public function addTranslation(TranslationInterface $translation);

    /**
     * @return TranslationInterface[]
     */
    public function getTranslations();
}
