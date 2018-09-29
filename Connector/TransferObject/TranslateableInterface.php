<?php

namespace PlentyConnector\Connector\TransferObject;

use PlentyConnector\Connector\ValueObject\Translation\Translation;

interface TranslateableInterface
{
    /**
     * @return Translation[]
     */
    public function getTranslations();
}
