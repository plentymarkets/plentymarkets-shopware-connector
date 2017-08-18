<?php

namespace ShopwareAdapter\DataPersister\Translation;

use PlentyConnector\Connector\TransferObject\Product\Product;

/**
 * Interface TranslationDataPersisterInterface
 */
interface TranslationDataPersisterInterface
{
    /**
     * @param Product $product
     */
    public function writeProductTranslations(Product $product);
}
