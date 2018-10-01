<?php

namespace ShopwareAdapter\DataPersister\Translation;

use PlentyConnector\Connector\TransferObject\Category\Category;
use PlentyConnector\Connector\TransferObject\Product\Product;

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
