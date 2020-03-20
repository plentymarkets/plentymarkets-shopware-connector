<?php

namespace ShopwareAdapter\DataPersister\Translation;

use Shopware\Models\Article\Image;
use SystemConnector\TransferObject\Category\Category;
use SystemConnector\TransferObject\Product\Product;

interface TranslationDataPersisterInterface
{
    public function writeProductTranslations(Product $product);

    public function writeCategoryTranslations(Category $category);

    public function removeMediaTranslation(Image $image);
}
