<?php

namespace PlentyConnector\Connector\TransferObject\Product\Stock;

use PlentyConnector\Connector\TransferObject\AbstractTransferObject;

/**
 * Class Product.
 */
class Stock extends AbstractTransferObject
{
    const TYPE = 'Stock';

    /**
     * Identifier of the object.
     *
     * @var string
     */
    private $identifier = '';

    /**
     * @var string
     */
    private $variationIdentifier = '';

    /**
     * @var float
     */
    private $stock = 0.0;

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param string $identifier
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * @return string
     */
    public function getVariationIdentifier()
    {
        return $this->variationIdentifier;
    }

    /**
     * @param string $variationIdentifier
     */
    public function setVariationIdentifier($variationIdentifier)
    {
        $this->variationIdentifier = $variationIdentifier;
    }

    /**
     * @return float
     */
    public function getStock()
    {
        return $this->stock;
    }

    /**
     * @param mixed float
     */
    public function setStock($stock)
    {
        $this->stock = $stock;
    }
}
