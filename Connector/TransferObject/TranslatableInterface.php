<?php

namespace SystemConnector\TransferObject;

use SystemConnector\ValueObject\Translation\Translation;

interface TranslatableInterface
{
    /**
     * @return Translation[]
     */
    public function getTranslations();
}
