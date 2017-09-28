<?php

namespace PlentymarketsAdapter\ReadApi;

use DateTimeImmutable;
use PlentymarketsAdapter\Client\Client;
use PlentymarketsAdapter\Client\Iterator\Iterator;
use PlentymarketsAdapter\Helper\LanguageHelperInterface;
use PlentymarketsAdapter\ReadApi\Item\Variation as VariationApi;

/**
 * Class Item
 */
class Item extends ApiAbstract
{
    /**
     * @var VariationApi
     */
    private $itemsVariationsApi;

    /**
     * @var LanguageHelperInterface
     */
    private $languageHelper;

    /**
     * @var string
     */
    private $includes = 'itemProperties.valueTexts,itemCrossSelling,itemImages';

    /**
     * Item constructor.
     *
     * @param Client                  $client
     * @param VariationApi            $itemsVariationsApi
     * @param LanguageHelperInterface $languageHelper
     */
    public function __construct(
        Client $client,
        VariationApi $itemsVariationsApi,
        LanguageHelperInterface $languageHelper
    ) {
        parent::__construct($client);

        $this->itemsVariationsApi = $itemsVariationsApi;
        $this->languageHelper = $languageHelper;
    }

    /**
     * @param $productId
     *
     * @return array
     */
    public function findOne($productId)
    {
        $result = $this->client->request('GET', 'items/' . $productId, [
            'lang' => $this->languageHelper->getLanguagesQueryString(),
            'with' => $this->includes,
        ]);

        if (empty($result)) {
            return $result;
        }

        $result['variations'] = $this->itemsVariationsApi->findBy(['itemId' => $result['id']]);
        $result['shippingProfiles'] = $this->getProductShippingProfiles($result['id']);

        return $result;
    }

    /**
     * @return Iterator
     */
    public function findAll()
    {
        return $this->client->getIterator('items', [
            'lang' => $this->languageHelper->getLanguagesQueryString(),
            'with' => $this->includes,
        ], function (array $elements) {
            $this->addAdditionalData($elements);

            return $elements;
        });
    }

    /**
     * @param $startTimestamp
     * @param $endTimestamp
     *
     * @return Iterator
     */
    public function findChanged(DateTimeImmutable $startTimestamp, DateTimeImmutable $endTimestamp)
    {
        $start = $startTimestamp->format(DATE_W3C);
        $end = $endTimestamp->format(DATE_W3C);

        return $this->client->getIterator('items', [
            'lang' => $this->languageHelper->getLanguagesQueryString(),
            'updatedBetween' => $start . ',' . $end,
            'with' => $this->includes,
        ], function (array $elements) {
            $this->addAdditionalData($elements);

            return $elements;
        });
    }

    /**
     * @param array $elements
     */
    private function addAdditionalData(array &$elements)
    {
        if (empty($elements)) {
            return;
        }

        $items = array_column($elements, 'id');

        $variations = $this->itemsVariationsApi->findBy(['itemId' => implode(',', $items)]);

        foreach ($elements as $key => $element) {
            $elements[$key]['variations'] = array_filter($variations, function (array $variation) use ($element) {
                return $element['id'] === $variation['itemId'];
            });

            $elements[$key]['shippingProfiles'] = $this->getProductShippingProfiles($element['id']);
        }
    }

    /**
     * @param int $id
     *
     * @return array
     */
    private function getProductShippingProfiles($id)
    {
        return $this->client->request('GET', 'items/' . $id . '/item_shipping_profiles', [
            'with' => 'names',
            'lang' => $this->languageHelper->getLanguagesQueryString(),
        ]);
    }
}
