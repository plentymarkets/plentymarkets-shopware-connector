<?php

namespace ShopwareAdapter\DataPersister\Translation;

use SystemConnector\TransferObject\Product\Product;

interface TranslationDataPersisterInterface
{
    /**
     * @param Product $product
     */
    public function writeProductTranslations(Product $product);
}
