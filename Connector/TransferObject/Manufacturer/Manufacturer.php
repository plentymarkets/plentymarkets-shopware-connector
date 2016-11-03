<?php

namespace PlentyConnector\Connector\TransferObject\Manufacturer;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\TransferObjectType;

/**
 * Class TransferObjects.
 */
class Manufacturer implements ManufacturerInterface
{
    /**
     * Identifier of the object.
     *
     * @var string
     */
    private $identifer;

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

        $this->identifer = $identifier;
        $this->name = $name;
        $this->logo = $logo;
        $this->link = $link;
    }

    /**
     * @return string
     */
    public static function getType()
    {
        return TransferObjectType::MANUFACTURER;
    }

    /**
     * {@inheritdoc}
     */
    public static function fromArray(array $params = [])
    {
        return new self(
            $params['identifier'],
            $params['name'],
            $params['logo'],
            $params['link']
        );
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifer;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }
}
