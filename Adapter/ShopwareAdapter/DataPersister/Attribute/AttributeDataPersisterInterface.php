<?php

namespace ShopwareAdapter\DataPersister\Attribute;

use Shopware\Models\Article\Detail;
use Shopware\Models\Article\Supplier;
use Shopware\Models\Category\Category;
use Shopware\Models\Media\Media;
use Shopware\Models\Order\Order;
use SystemConnector\ValueObject\Attribute\Attribute;

interface AttributeDataPersisterInterface
{
    /**
     * @param Attribute[] $attributes
     */
    public function saveProductDetailAttributes(Detail $detail, array $attributes = []);

    /**
     * @param Attribute[] $attributes
     */
    public function saveCategoryAttributes(Category $category, array $attributes = []);

    /**
     * @param Attribute[] $attributes
     */
    public function saveOrderAttributes(Order $order, array $attributes = []);

    /**
     * @param Attribute[] $attributes
     */
    public function saveManufacturerAttributes(Supplier $supplier, array $attributes = []);

    /**
     * @param Attribute[] $attributes
     */
    public function saveMediaAttributes(Media $media, array $attributes = []);
}
