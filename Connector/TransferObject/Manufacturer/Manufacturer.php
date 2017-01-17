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
    private $logoIdentifier;

    /**
     * @var string
     */
    private $link;

    /**
     * Manufacturer constructor.
     *
     * @param string $identifier
     * @param string $name
     * @param string|null $logoIdentifier
     * @param string|null $link
     */
    public function __construct($identifier, $name, $logoIdentifier = null, $link = null)
    {
        Assertion::uuid($identifier);
        Assertion::string($name);
        Assertion::nullOrUuid($logoIdentifier);
        Assertion::nullOrUrl($link);

        $this->identifier = $identifier;
        $this->name = $name;
        $this->logoIdentifier = $logoIdentifier;
        $this->link = $link;
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
    public static function fromArray(array $params = [])
    {
        Assertion::allInArray(array_keys($params), [
            'identifier',
            'name',
            'logoIdentifier',
            'link',
        ]);

        return new self(
            $params['identifier'],
            $params['name'],
            $params['logoIdentifier'],
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
    public function getLogoIdentifier()
    {
        return $this->logoIdentifier;
    }

    /**
     * {@inheritdoc}
     */
    public function getLink()
    {
        return $this->link;
    }
}
