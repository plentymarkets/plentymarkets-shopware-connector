<?php

namespace ShopwareAdapter\DataProvider\Translation;

use PlentyConnector\Connector\TransferObject\Product\Property\Property;
use PlentyConnector\Connector\TransferObject\Product\Property\Value\Value;
use PlentyConnector\Connector\ValueObject\Identity\Identity;
use Shopware\Models\Property\Option as OptionModel;
use Shopware\Models\Property\Value as ValueModel;
use Shopware\Models\Shop\Shop as ShopModel;

/**
 * Interface TranslationDataProviderInterface
 */
interface TranslationDataProviderInterface
{
    /**
     * @param Identity $identity
     *
     * @return ShopModel[]
     */
    public function getShopsByLocaleIdentitiy(Identity $identity);

    /**
     * @param Property $property
     *
     * @return OptionModel
     */
    public function getPropertyOptionByName(Property $property);

    /**
     * @param Value $value
     *
     * @return ValueModel
     */
    public function getPropertyValueByValue(Value $value);
}
