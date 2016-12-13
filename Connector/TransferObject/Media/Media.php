<?php

namespace PlentyConnector\Connector\TransferObject\Media;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\TransferObjectType;
use PlentyConnector\Connector\TransferObject\Translation\TranslationInterface;

/**
 * Class Media
 */
class Media implements MediaInterface
{
    /**
     * @var string
     */
    private $identifier;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $alternativeName;

    /**
     * @var string
     */
    private $link;

    /**
     * @var string
     */
    private $hash;

    /**
     * @var TranslationInterface[]
     */
    private $translations;

    /**
     * Media constructor.
     *
     * @param string $identifier
     * @param string $name
     * @param string $alternativeName
     * @param string $link
     * @param string $hash
     */
    public function __construct(
        $identifier,
        $name,
        $alternativeName,
        $link,
        $hash = null
    ) {
        Assertion::uuid($identifier);
        Assertion::string($name);
        Assertion::string($alternativeName);
        Assertion::url($link);
        Assertion::readable($link);
        Assertion::nullOrString($hash);

        $this->identifier = $identifier;
        $this->name = $name;
        $this->alternativeName = $alternativeName;
        $this->link = $link;

        if (null === $hash) {
            $hash = sha1_file($link);
        }

        $this->hash = $hash;
    }

    /**
     * {@inheritdoc}
     */
    public static function getType()
    {
        return TransferObjectType::MEDIA;
    }

    /**
     * @param array $params
     *
     * @return self
     */
    public static function fromArray(array $params = [])
    {
        Assertion::allInArray(array_keys($params), [
            'identifier',
            'name',
            'link',
        ]);

        return new self(
            $params['identifier'],
            $params['name'],
            $params['link']
        );
    }

    /**
     * @param TranslationInterface $translation
     */
    public function addTranslation(TranslationInterface $translation)
    {
        // TODO: Implement addTranslation() method.
    }

    /**
     * @return TranslationInterface[]
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getAlternativeName()
    {
        return $this->alternativeName;
    }

    /**
     * {@inheritdoc}
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * {@inheritdoc}
     */
    public function getHash()
    {
        return $this->hash;
    }
}
