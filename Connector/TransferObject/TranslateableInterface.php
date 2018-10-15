<?php

namespace SystemConnector\TransferObject;

use SystemConnector\ValueObject\Translation\Translation;

interface TranslateableInterface
{
    /**
     * @return Translation[]
     */
    public function getTranslations();
}
