<?php

namespace PlentyConnector\Connector\TransferObject\Product;

use PlentyConnector\Connector\TransferObject\Product\LinkedProduct\LinkedProductInterface;
use PlentyConnector\Connector\TransferObject\Product\Property\PropertyInterface;
use PlentyConnector\Connector\TransferObject\Product\Variation\VariationInterface;
use PlentyConnector\Connector\TransferObject\TransferObjectInterface;
use PlentyConnector\Connector\TransferObject\TranslateableInterface;
use PlentyConnector\Connector\ValueObject\Attribute\AttributeInterface;

/**
 * Interface ProductInterface
 */
interface ProductInterface extends TransferObjectInterface, TranslateableInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getNumber();

    /**
     * @return bool
     */
    public function isActive();

    /**
     * @return int
     */
    public function getStock();

    /**
     * @return string
     */
    public function getManufacturerIdentifier();

    /**
     * @return array
     */
    public function getCategoryIdentifiers();

    /**
     * @return array
     */
    public function getDefaultCategoryIdentifiers();

    /**
     * @return array
     */
    public function getShippingProfileIdentifiers();

    /**
     * @return VariationInterface[]
     */
    public function getVariations();

    /**
     * @return string
     */
    public function getVatRateIdentifier();

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @return string
     */
    public function getLongDescription();

    /**
     * @return string
     */
    public function getTechnicalDescription();

    /**
     * @return string
     */
    public function getMetaTitle();

    /**
     * @return string
     */
    public function getMetaDescription();

    /**
     * @return string
     */
    public function getMetaKeywords();

    /**
     * @return string
     */
    public function getMetaRobots();

    /**
     * @return LinkedProductInterface[]
     */
    public function getLinkedProducts();

    /**
     * @return array
     */
    public function getDocuments();

    /**
     * Product Properties. Example: IncludesBatteries=true
     *
     * @return PropertyInterface[]
     */
    public function getProperties();

    /**
     * @return AttributeInterface[]
     */
    public function getAttributes();
}
