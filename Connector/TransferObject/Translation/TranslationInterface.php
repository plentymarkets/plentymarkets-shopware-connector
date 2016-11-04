<?php

namespace PlentyConnector\Connector\TransferObject\Translation;

use PlentyConnector\Connector\TransferObject\TransferObjectInterface;

/**
 * Interface TranslationInterface
 */
interface TranslationInterface extends TransferObjectInterface
{
    /**
     * Get locale
     *
     * @return string
     */
    public function getLocale();

    /**
     * Get property
     *
     * @return string
     */
    public function getProperty();

    /**
     * Get value
     *
     * @return string
     */
    public function getValue();
}
