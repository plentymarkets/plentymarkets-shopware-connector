<?php

namespace PlentymarketsAdapter\ReadApi\Item;

use PlentymarketsAdapter\Helper\LanguageHelper;
use PlentymarketsAdapter\ReadApi\ApiAbstract;

/**
 * Class ShippingProfile
 */
class ShippingProfile extends ApiAbstract
{
    /**
     * @param $productId
     *
     * @return array
     */
    public function findOne($productId)
    {
        $languageHelper = new LanguageHelper();

        return $this->client->request(
            'GET',
            'items/' . $productId . '/item_shipping_profiles',
            [
                'with' => 'names',
                'lang' => $languageHelper->getLanguagesQueryString(),
            ]
        );
    }
}
