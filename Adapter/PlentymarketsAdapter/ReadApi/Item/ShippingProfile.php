<?php

namespace PlentymarketsAdapter\ReadApi\Item;

use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\Helper\LanguageHelperInterface;
use PlentymarketsAdapter\ReadApi\ApiAbstract;

/**
 * Class ShippingProfile
 */
class ShippingProfile extends ApiAbstract
{
    /**
     * @var LanguageHelperInterface
     */
    private $languageHelper;

    /**
     * ShippingProfile constructor.
     *
     * @param ClientInterface         $client
     * @param LanguageHelperInterface $languageHelper
     */
    public function __construct(
        ClientInterface $client,
        LanguageHelperInterface $languageHelper
    ) {
        parent::__construct($client);

        $this->languageHelper = $languageHelper;
    }

    /**
     * @param $productId
     *
     * @return array
     */
    public function findOne($productId)
    {
        return $this->client->request('GET', 'items/' . $productId . '/item_shipping_profiles', [
            'with' => 'names',
            'lang' => $this->languageHelper->getLanguagesQueryString(),
        ]);
    }
}
