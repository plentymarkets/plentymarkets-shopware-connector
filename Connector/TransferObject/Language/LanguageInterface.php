<?php

namespace PlentyConnector\Connector\TransferObject\Language;

use PlentyConnector\Connector\TransferObject\TransferObjectInterface;

/**
 * Interface LanguageInterface
 */
interface LanguageInterface extends TransferObjectInterface
{
    /**
     * @return string
     */
    public function getName();
}
