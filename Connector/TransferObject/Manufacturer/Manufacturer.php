<?php

namespace PlentyConnector\Connector\TransferObject\Manufacturer;

use Assert\Assertion;

/**
 * Class TransferObjects.
 */
class Manufacturer implements ManufacturerInterface
{
    const TYPE = 'Manufacturer';

    /**
     * Identifier of the object.
     *
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
    private $logo;

    /**
     * @var string
     */
    private $link;

    /**
     * Manufacturer constructor.
     *
     * @param string $identifier
     * @param string $name
     * @param string $logo
     * @param string $link
     */
    public function __construct($identifier, $name, $logo = null, $link = null)
    {
        Assertion::uuid($identifier);
        Assertion::string($name);
        Assertion::nullOrUrl($logo);
        Assertion::nullOrUrl($link);

        $this->identifier = $identifier;
        $this->name = $name;
        $this->logo = $logo;
        $this->link = $link;
    }

    /**
     * {@inheritdoc}
     */
    public static function getType()
    {
        return self::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public static function fromArray(array $params = [])
    {
        Assertion::allInArray(array_keys($params), [
            'identifier',
            'name',
            'logo',
            'link',
        ]);

        return new self(
            $params['identifier'],
            $params['name'],
            $params['logo'],
            $params['link']
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
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * {@inheritdoc}
     */
    public function getLink()
    {
        return $this->link;
    }
}
