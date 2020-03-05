<?php

namespace SystemConnector\ValueObject\Attribute;

use Shopware\Bundle\AttributeBundle\Service\TypeMapping;
use SystemConnector\TransferObject\TranslatableInterface;
use SystemConnector\ValueObject\AbstractValueObject;
use SystemConnector\ValueObject\Translation\Translation;

class Attribute extends AbstractValueObject implements TranslatableInterface
{
    /**
     * @var string
     */
    private $key = '';

    /**
     * @var string
     */
    private $value = '';

    /**
     * @var string
     */
    private $type = TypeMapping::TYPE_TEXT;

    /**
     * @var Translation[]
     */
    private $translations = [];

    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    public function getValue(): string
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

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type)
    {
        $this->type = $type;
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
    public function setTranslations($translations)
    {
        $this->translations = $translations;
    }

    /**
     * {@inheritdoc}
     */
    public function getClassProperties()
    {
        return [
            'key' => $this->getKey(),
            'value' => $this->getValue(),
            'translations' => $this->getTranslations(),
        ];
    }
}
