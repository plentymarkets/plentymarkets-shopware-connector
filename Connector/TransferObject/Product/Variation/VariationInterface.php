<?php

namespace PlentyConnector\Connector\TransferObject\Product\Variation;

use PlentyConnector\Connector\TransferObject\Product\Price\PriceInterface;
use PlentyConnector\Connector\TransferObject\Product\Property\PropertyInterface;
use PlentyConnector\Connector\ValueObject\Attribute\AttributeInterface;
use PlentyConnector\Connector\ValueObject\ValueObjectInterface;

/**
 * Interface VariationInterface
 */
interface VariationInterface extends ValueObjectInterface
{
    /**
     * @return bool
     */
    public function getActive();

    /**
     * @return bool
     */
    public function isIsMain();

    /**
     * @return int
     */
    public function getStock();

    /**
     * @return string
     */
    public function getNumber();

    /**
     * @return array
     */
    public function getImageIdentifiers();

    /**
     * @return PriceInterface[]
     */
    public function getPrices();

    /**
     * @return string
     */
    public function getUnitIdentifier();

    /**
     * @return mixed
     */
    public function getContent();

    /**
     * @return string
     */
    public function getPackagingUnit();

    /**
     * @return AttributeInterface[]
     */
    public function getAttributes();

    /**
     * @return PropertyInterface[]
     */
    public function getProperties();
}
