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
     * @param Identity $identity
     *
     * @return ShopModel[]
     */
    public function getShopsByLocaleIdentity(Identity $identity);

    /**
     * @param Property $property
     *
     * @return null|OptionModel
     */
    public function getPropertyOptionByName(Property $property);

    /**
     * @param Value $value
     *
     * @return null|ValueModel
     */
    public function getPropertyValueByValue(Value $value);

    /**
     * @param Property $property
     *
     * @return null|ConfiguratorGroupModel
     */
    public function getConfigurationGroupByName(Property $property);

    /**
     * @param Value $value
     *
     * @return null|ConfiguratorOptionModel
     */
    public function getConfigurationOptionByName(Value $value);

    /**
     * @param Identity $mediaIdentity
     * @param $articleId
     *
     * @return Image
     */
    public function getArticleImage(Identity $mediaIdentity, $articleId);
}
