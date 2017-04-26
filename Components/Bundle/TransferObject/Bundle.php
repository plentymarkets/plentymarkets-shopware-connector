<?php

namespace PlentyConnector\Components\Bundle\TransferObject;

use PlentyConnector\Components\Bundle\TransferObject\BundleProduct\BundleProduct;
use PlentyConnector\Connector\TransferObject\AbstractTransferObject;
use PlentyConnector\Connector\TransferObject\Product\Product;

/**
 * Class Bundle
 */
class Bundle extends AbstractTransferObject
{
    const TYPE = 'Bundle';

    /**
     * Identifier of the object.
     *
     * @var string
     */
    private $identifier = '';

    /**
     * @var Product
     */
    private $product;

    /**
     * @var BundleProduct[]
     */
    private $bundleProducts = [];

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
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param Product $product
     */
    public function setProduct($product)
    {
        $this->product = $product;
    }

    /**
     * @return BundleProduct[]
     */
    public function getBundleProducts()
    {
        return $this->bundleProducts;
    }

    /**
     * @param BundleProduct[] $bundleProducts
     */
    public function setBundleProducts(array $bundleProducts = [])
    {
        $this->bundleProducts = $bundleProducts;
    }
}
