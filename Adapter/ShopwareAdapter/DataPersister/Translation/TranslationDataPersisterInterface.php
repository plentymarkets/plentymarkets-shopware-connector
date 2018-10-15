<?php

namespace ShopwareAdapter\DataPersister\Translation;

use SystemConnector\TransferObject\Category\Category;
use SystemConnector\TransferObject\Product\Product;

interface TranslationDataPersisterInterface
{
    /**
     * @param Product $product
     */
    public function writeProductTranslations(Product $product);

    /**
     * @param Category $category
     */
    public function writeCategoryTranslations(Category $category);
}
