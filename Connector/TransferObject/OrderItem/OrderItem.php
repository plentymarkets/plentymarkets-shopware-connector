<?php

namespace PlentyConnector\Connector\TransferObject\OrderItem;

use Assert\Assertion;
use PlentyConnector\Connector\TransferObject\AbstractTransferObject;

/**
 * Class OrderItem
 */
class OrderItem extends AbstractTransferObject
{
    const TYPE = 'OrderItem';

    /**
     * @var string
     */
    private $identifier = '';

    /**
     * @var int
     */
    private $quantity = 1;

    /**
     * @var string
     */
    private $productId = '';

    /**
     * @var string
     */
    private $variationId = '';

    /**
     * @var string
     */
    private $name = '';

    /**
     * @var float
     */
    private $price = 0.0;

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
    public function getIdentifier()
    {
        Assertion::notBlank($this->identifier);

        return $this->identifier;
    }

    /**
     * @param string $identifier
     */
    public function setIdentifier($identifier)
    {
        Assertion::uuid($identifier);

        $this->identifier = $identifier;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     */
    public function setQuantity($quantity)
    {
        Assertion::integer($quantity);

        $this->quantity = $quantity;
    }

    /**
     * @return string
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * @param string $productId
     */
    public function setProductId($productId)
    {
        Assertion::string($productId);

        $this->productId = $productId;
    }

    /**
     * @return string
     */
    public function getVariationId()
    {
        return $this->variationId;
    }

    /**
     * @param string $variationId
     */
    public function setVariationId($variationId)
    {
        Assertion::string($variationId);

        $this->variationId = $variationId;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        Assertion::string($name);

        $this->name = $name;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param float $price
     */
    public function setPrice($price)
    {
        Assertion::numeric($price);

        $this->price = $price;
    }
}
