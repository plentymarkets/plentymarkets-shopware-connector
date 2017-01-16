<?php

namespace PlentyConnector\Connector\TransferObject;

use PlentyConnector\Connector\TransferObject\Translation\TranslationInterface;

/**
 * Interface TranslateableInterface
 */
interface TranslateableInterface
{
    /**
     * @return TranslationInterface[]
     */
    public function getTranslations();
}
