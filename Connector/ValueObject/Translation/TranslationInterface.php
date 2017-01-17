<?php

namespace PlentyConnector\Connector\ValueObject\Translation;

use PlentyConnector\Connector\ValueObject\ValueObjectInterface;

/**
 * Interface TranslationInterface
 */
interface TranslationInterface extends ValueObjectInterface
{
    /**
     * return the language identifier
     *
     * @return string
     */
    public function getLanguageIdentifier();

    /**
     * Get the property
     *
     * @return string
     */
    public function getProperty();

    /**
     * Get the value
     *
     * @return string
     */
    public function getValue();
}
