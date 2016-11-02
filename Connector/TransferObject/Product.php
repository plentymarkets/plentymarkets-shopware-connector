<?php

namespace PlentyConnector\Connector\TransferObject;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\Manufacturer\ManufacturerInterface;

/**
 * Class Product
 *
 * @package PlentyConnector\Connector\TransferObject
 */
class Product implements TransferObjectInterface
{
    /**
     * Identifier of the object
     *
     * @var string
     */
    private $identifer;

    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $variants;

    /**
     * @var ManufacturerInterface
     */
    private $manufacturer;

    /**
     * Product constructor.
     *
     * @param string $identifier
     * @param string $name
     * @param array $variants
     * @param ManufacturerInterface $manufacturer
     */
    public function __construct($identifier, $name, array $variants = [], ManufacturerInterface $manufacturer)
    {
        Assertion::uuid($identifier);
        Assertion::string($name);
        Assertion::isArray($variants);
        Assertion::isInstanceOf($manufacturer, ManufacturerInterface::class);

        $this->identifer = $identifier;
        $this->name = $name;
        $this->variants = $variants;
        $this->manufacturer = $manufacturer;
    }

    /**
     * @inheritdoc
     */
    public static function getType()
    {
        return 'Product';
    }

    /**
     * @param array $params
     *
     * @return self
     */
    public static function fromArray(array $params = [])
    {
        return new self(
            $params['identifier'],
            $params['name'],
            $params['variants'],
            $params['manufacturer']
        );
    }

    /**
     * @return string
     */
    public function getIdentifer()
    {
        return $this->identifer;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getVariants()
    {
        return $this->variants;
    }

    /**
     * @return ManufacturerInterface
     */
    public function getManufacturer()
    {
        return $this->manufacturer;
    }
}
