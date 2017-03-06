<?php

namespace PlentyConnector\Connector\TransferObject;

use PlentyConnector\Connector\ValueObject\Translation\Translation;

/**
 * Interface TranslateableInterface
 */
interface TranslateableInterface
{
    /**
     * @return Translation[]
     */
    public function getTranslations();
}
