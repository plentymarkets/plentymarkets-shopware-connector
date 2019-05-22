<?php

namespace SystemConnector\TransferObject\Product\Property\Value;

use SystemConnector\TransferObject\TranslatableInterface;
use SystemConnector\ValueObject\AbstractValueObject;
use SystemConnector\ValueObject\Translation\Translation;

class Value extends AbstractValueObject implements TranslatableInterface
{
    /**
     * @var string
     */
    private $value = '';

    /**
     * @var int
     */
    private $position = 0;

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
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param int $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
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

    /**
     * {@inheritdoc}
     */
    public function getClassProperties()
    {
        return [
            'value' => $this->getValue(),
            'position' => $this->getPosition(),
            'translations' => $this->getTranslations(),
        ];
    }
}
