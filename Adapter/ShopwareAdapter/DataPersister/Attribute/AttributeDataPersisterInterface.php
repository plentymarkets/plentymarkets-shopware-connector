<?php

namespace ShopwareAdapter\DataPersister\Attribute;

use Shopware\Models\Article\Detail;
use Shopware\Models\Article\Supplier;
use Shopware\Models\Category\Category;
use Shopware\Models\Media\Media;
use Shopware\Models\Order\Order;

/**
 * Interface AttributeDataPersisterInterface
 */
interface AttributeDataPersisterInterface
{
    /**
     * @param Detail      $detail
     * @param Attribute[] $attributes
     */
    public function saveProductDetailAttributes(Detail $detail, array $attributes = []);

    /**
     * @param Category    $category
     * @param Attribute[] $attributes
     */
    public function saveCategoryAttributes(Category $category, array $attributes = []);

    /**
     * @param Order       $order
     * @param Attribute[] $attributes
     */
    public function saveOrderAttributes(Order $order, array $attributes = []);

    /**
     * @param Supplier    $supplier
     * @param Attribute[] $attributes
     */
    public function saveManufacturerAttributes(Supplier $supplier, array $attributes = []);

    /**
     * @param Media       $media
     * @param Attribute[] $attributes
     */
    public function saveMediaAttributes(Media $media, array $attributes = []);
}
