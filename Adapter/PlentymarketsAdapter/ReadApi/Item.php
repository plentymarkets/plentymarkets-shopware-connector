<?php

namespace PlentymarketsAdapter\ReadApi;

use DateTimeImmutable;
use PlentymarketsAdapter\Client\Client;
use PlentymarketsAdapter\Client\Iterator\Iterator;
use PlentymarketsAdapter\Helper\LanguageHelper;
use PlentymarketsAdapter\ReadApi\Item\Variation;

/**
 * Class Item
 */
class Item extends ApiAbstract
{
    /**
     * @var Variation
     */
    private $itemsVariationsApi;

    /**
     * Item constructor.
     *
     * @param Client    $client
     * @param Variation $itemsVariationsApi
     */
    public function __construct(
        Client $client,
        Variation $itemsVariationsApi
    ) {
        parent::__construct($client);

        $this->itemsVariationsApi = $itemsVariationsApi;
    }

    /**
     * @param $productId
     *
     * @return array
     */
    public function findOne($productId)
    {
        $languageHelper = new LanguageHelper();

        $result = $this->client->request('GET', 'items/' . $productId, [
            'lang' => $languageHelper->getLanguagesQueryString(),
            'with' => 'itemProperties.valueTexts,itemCrossSelling',
        ]);

        $this->addAdditionalData($result);

        return $result;
    }

    /**
     * @return Iterator
     */
    public function findAll()
    {
        $languageHelper = new LanguageHelper();

        return $this->client->getIterator('items', [
            'lang' => $languageHelper->getLanguagesQueryString(),
            'with' => 'itemProperties.valueTexts,itemCrossSelling',
        ], function ($element) {
            $this->addAdditionalData($element);

            return $element;
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

        $languageHelper = new LanguageHelper();

        return $this->client->getIterator('items', [
            'lang' => $languageHelper->getLanguagesQueryString(),
            'updatedBetween' => $start . ',' . $end,
            'with' => 'itemProperties.valueTexts,itemCrossSelling',
        ], function ($element) {
            $this->addAdditionalData($element);

            return $element;
        });
    }

    /**
     * @param array $element
     */
    private function addAdditionalData(array &$element)
    {
        $element['variations'] = $this->itemsVariationsApi->findBy(['itemId' => $element['id']]);
    }
}
