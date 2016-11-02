<?php

namespace PlentyConnector\Connector\TransferObject\Stock;

use Assert\Assertion;

/**
 * Class Stock
 *
 * @package PlentyConnector\Connector\TransferObject
 */
class Stock implements StockInterface
{
    /**
     * @var string
     */
    private $productIdentifier;

    /**
     * @var string
     */
    private $variationIdentifier;

    /**
     * @var int
     */
    private $stock;

    /**
     * Stock constructor.
     *
     * @param string $productIdentifier
     * @param null $variationIdentifier
     *
     * @param int $stock
     */
    public function __construct($productIdentifier, $variationIdentifier = null, $stock)
    {
        Assertion::uuid($productIdentifier);
        Assertion::nullOrUuid($variationIdentifier);
        Assertion::integer($stock);

        $this->productIdentifier = $productIdentifier;
        $this->variationIdentifier = $variationIdentifier;
        $this->stock = $stock;
    }

    /**
     * @return string
     */
    public static function getType()
    {
        return 'Stock';
    }

    /**
     * @param array $params
     *
     * @return self
     */
    public static function fromArray(array $params = [])
    {
        return new self(
            $params['productIdentifier'],
            $params['variationIdentifier'],
            $params['stock']
        );
    }

    /**
     * @return string
     */
    public function getProductIdentifier()
    {
        return $this->productIdentifier;
    }

    /**
     * @return string
     */
    public function getVariationIdentifier()
    {
        return $this->variationIdentifier;
    }

    /**
     * @return int
     */
    public function getStock()
    {
        return $this->stock;
    }
}
