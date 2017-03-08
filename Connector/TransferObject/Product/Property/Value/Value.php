<?php

namespace PlentyConnector\Connector\TransferObject\Product\Property\Value;

use PlentyConnector\Connector\TransferObject\TranslateableInterface;
use PlentyConnector\Connector\ValueObject\AbstractValueObject;
use PlentyConnector\Connector\ValueObject\Translation\Translation;

/**
 * Class Value
 */
class Value extends AbstractValueObject implements TranslateableInterface
{
    /**
     * @var string
     */
    private $value = [];

    /**
     * @var Translation[]
     */
    private $translations = [];

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return Translation[]
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * @param Translation[] $translations
     */
    public function setTranslations(array $translations)
    {
        $this->translations = $translations;
    }
}
