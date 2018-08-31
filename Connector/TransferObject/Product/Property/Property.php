<?php

namespace PlentyConnector\Connector\TransferObject\Product\Property;

use PlentyConnector\Connector\TransferObject\Product\Property\Value\Value;
use PlentyConnector\Connector\TransferObject\TranslateableInterface;
use PlentyConnector\Connector\ValueObject\AbstractValueObject;
use PlentyConnector\Connector\ValueObject\Translation\Translation;

/**
 * Class Property
 */
class Property extends AbstractValueObject implements TranslateableInterface
{
    /**
     * @var string
     */
    private $name = '';

    /**
     * @var int
     */
    private $position = 0;

    /**
     * @var Value[]
     */
    private $values = [];

    /**
     * @var Translation[]
     */
    private $translations = [];

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param int position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return Value[]
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * @param Value[] $values
     */
    public function setValues($values)
    {
        $this->values = $values;
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
