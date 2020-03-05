<?php

namespace ShopwareAdapter\DataProvider\Translation;

use Shopware\Models\Article\Configurator\Group as ConfiguratorGroupModel;
use Shopware\Models\Article\Configurator\Option as ConfiguratorOptionModel;
use Shopware\Models\Article\Image;
use Shopware\Models\Property\Option as OptionModel;
use Shopware\Models\Property\Value as ValueModel;
use Shopware\Models\Shop\Shop as ShopModel;
use SystemConnector\IdentityService\Struct\Identity;
use SystemConnector\TransferObject\Product\Property\Property;
use SystemConnector\TransferObject\Product\Property\Value\Value;

interface TranslationDataProviderInterface
{
    /**
     * @return ShopModel[]
     */
    public function getShopsByLocaleIdentity(Identity $identity): array;

    /**
     * @return null|OptionModel
     */
    public function getPropertyOptionByName(Property $property);

    /**
     * @return null|ValueModel
     */
    public function getPropertyValueByValue(Value $value);

    /**
     * @return null|ConfiguratorGroupModel
     */
    public function getConfigurationGroupByName(Property $property);

    /**
     * @return null|ConfiguratorOptionModel
     */
    public function getConfigurationOptionByName(Value $value);

    /**
     * @param $articleId
     *
     * @return null|Image
     */
    public function getArticleImage(Identity $mediaIdentity, $articleId);
}
