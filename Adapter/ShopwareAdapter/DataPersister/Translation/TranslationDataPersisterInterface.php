<?php

namespace ShopwareAdapter\DataPersister\Translation;

use Shopware\Models\Article\Image;
use SystemConnector\TransferObject\Category\Category;
use SystemConnector\TransferObject\Product\Product;
use SystemConnector\TransferObject\Product\Variation\Variation;

interface TranslationDataPersisterInterface
{
    /**
     * @param Product $product
     */
    public function writeProductTranslations(Product $product);

    /**
     * @param Variation $variation
     */
    public function writeProductDetailTranslations(Variation $variation);

    /**
     * @param Category $category
     */
    public function writeCategoryTranslations(Category $category);

    /**
     * @param Image $image
     */
    public function removeMediaTranslation(Image $image);
}
