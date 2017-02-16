<?php

namespace PlentyConnector\Connector\TransferObject\Product\Variation;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\Product\Price\PriceInterface;
use PlentyConnector\Connector\TransferObject\Product\Property\PropertyInterface;
use PlentyConnector\Connector\ValueObject\Attribute\AttributeInterface;

/**
 * Class Variation.
 */
class Variation implements VariationInterface
{
    /**
     * @var bool
     */
    private $active;

    /**
     * @var bool
     */
    private $isMain;

    /**
     * @var int
     */
    private $stock;

    /**
     * @var string
     */
    private $number;

    /**
     * @var array
     */
    private $imageIdentifiers;

    /**
     * @var PriceInterface[]
     */
    private $prices;

    /**
     * @var string
     */
    private $unitIdentifier;

    /**
     * @var float
     */
    private $content;

    /**
     * @var string
     */
    private $packagingUnit;

    /**
     * @var AttributeInterface[]
     */
    private $attributes;

    /**
     * @var PropertyInterface[]
     */
    private $properties;

    /**
     * Variation constructor.
     *
     * @param bool $active
     * @param bool $isMain
     * @param int $stock
     * @param string $number
     * @param array $imageIdentifiers
     * @param PriceInterface[] $prices
     * @param string $unitIdentifier
     * @param float $content
     * @param string $packagingUnit
     * @param AttributeInterface[] $attributes
     * @param PropertyInterface[] $properties
     */
    public function __construct(
        $active,
        $isMain,
        $stock,
        $number,
        array $imageIdentifiers,
        array $prices,
        $unitIdentifier,
        $content,
        $packagingUnit,
        array $attributes = [],
        array $properties = []
    ) {
        Assertion::boolean($active);
        Assertion::boolean($isMain);
        Assertion::integer($stock);
        Assertion::string($number);
        Assertion::allUuid($imageIdentifiers);
        Assertion::allIsInstanceOf($prices, PriceInterface::class);
        Assertion::uuid($unitIdentifier);
        Assertion::float($content);
        Assertion::string($packagingUnit);
        Assertion::allIsInstanceOf($attributes, AttributeInterface::class);
        Assertion::allIsInstanceOf($properties, PropertyInterface::class);

        $this->active = $active;
        $this->isMain = $isMain;
        $this->stock = $stock;
        $this->number = $number;
        $this->imageIdentifiers = $imageIdentifiers;
        $this->prices = $prices;
        $this->unitIdentifier = $unitIdentifier;
        $this->content = $content;
        $this->packagingUnit = $packagingUnit;
        $this->attributes = $attributes;
        $this->properties = $properties;
    }

    /**
     * {@inheritdoc}
     */
    public static function fromArray(array $params = [])
    {
        Assertion::allInArray(array_keys($params), [
            'active',
            'isMain',
            'stock',
            'number',
            'imageIdentifiers',
            'prices',
            'unitIdentifier',
            'content',
            'packagingUnit',
            'attributes',
            'properties',
        ]);

        return new self(
            $params['active'],
            $params['isMain'],
            $params['stock'],
            $params['number'],
            $params['imageIdentifiers'],
            $params['prices'],
            $params['unitIdentifier'],
            $params['content'],
            $params['packagingUnit'],
            $params['attributes'],
            $params['properties']
        );
    }

    /**
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @return bool
     */
    public function isIsMain()
    {
        return $this->isMain;
    }

    /**
     * @return int
     */
    public function getStock()
    {
        return $this->stock;
    }

    /**
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @return array
     */
    public function getImageIdentifiers()
    {
        return $this->imageIdentifiers;
    }

    /**
     * @return PriceInterface[]
     */
    public function getPrices()
    {
        return $this->prices;
    }

    /**
     * @return string
     */
    public function getUnitIdentifier()
    {
        return $this->unitIdentifier;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return string
     */
    public function getPackagingUnit()
    {
        return $this->packagingUnit;
    }

    /**
     * Gets Attributes for the variation. Can be used to transport additional data from and to the adapters
     *
     * @return AttributeInterface[]
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Gets the variation characteristics in form of properties. Example: Color=Black, Size=XL
     *
     * @return PropertyInterface[]
     */
    public function getProperties()
    {
        return $this->properties;
    }
}
