<?php

namespace SystemConnector\TransferObject\Product\Property;

use SystemConnector\TransferObject\Product\Property\Value\Value;
use SystemConnector\TransferObject\TranslatableInterface;
use SystemConnector\ValueObject\AbstractValueObject;
use SystemConnector\ValueObject\Translation\Translation;

class Property extends AbstractValueObject implements TranslatableInterface
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
    public function getName(): string
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
    public function getPosition(): int
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
     * @return Value[]
     */
    public function getValues(): array
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
    public function getTranslations(): array
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
            'name' => $this->getName(),
            'position' => $this->getPosition(),
            'values' => $this->getValues(),
            'translations' => $this->getTranslations(),
        ];
    }
}
