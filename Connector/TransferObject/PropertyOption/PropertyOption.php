<?php

namespace PlentyConnector\Connector\ValueObject\PropertyOption;

use Assert\Assertion;
use PlentyConnector\Connector\ValueObject\Translation\Translation;
use PlentyConnector\Connector\ValueObject\Translation\TranslationInterface;

/**
 * Class PropertyOption
 */
class PropertyOption implements PropertyOptionInterface
{
    const TYPE = 'PropertyOption';

    /**
     * @var string
     */
    private $identifier;

    /**
     * @var string
     */
    private $groupIdentifier;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $value;

    /**
     * @var array
     */
    private $translations;

    /**
     * PropertyOption constructor.
     *
     * @param string $identifier
     * @param string $groupIdentifier
     * @param string $name
     * @param string $value
     * @param TranslationInterface[] $translations
     */
    public function __construct($identifier, $groupIdentifier, $name, $value, array $translations = [])
    {
        Assertion::uuid($groupIdentifier);
        Assertion::uuid($identifier);
        Assertion::string($name);
        Assertion::notBlank($name);
        Assertion::string($value);
        Assertion::allIsInstanceOf($translations, TranslationInterface::class);

        $this->identifier = $identifier;
        $this->groupIdentifier = $groupIdentifier;
        $this->name = $name;
        $this->value = $value;
        $this->translations = $translations;
    }

    /**
     * {@inheritdoc}
     */
    public static function fromArray(array $params = [])
    {
        Assertion::allInArray(array_keys($params), [
            'identifier',
            'groupIdentifier',
            'name',
            'value',
            'translations'
        ]);

        return new self(
            $params['identifier'],
            $params['groupIdentifier'],
            $params['name'],
            $params['value'],
            $params['translations']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @return mixed
     */
    public function getGroupIdentifier()
    {
        return $this->groupIdentifier;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function getTranslations()
    {
        return $this->translations;
    }
}
