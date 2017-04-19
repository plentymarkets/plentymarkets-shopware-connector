<?php

namespace PlentymarketsAdapter\ReadApi;

use DateTimeImmutable;
use PlentymarketsAdapter\Helper\LanguageHelper;

/**
 * Class Item
 */
class Item extends ApiAbstract
{
    /**
     * @param $productId
     *
     * @return array
     */
    public function findOne($productId)
    {
        $languageHelper = new LanguageHelper();

        return $this->client->request('GET', 'items/' . $productId, [
            'lang' => $languageHelper->getLanguagesQueryString(),
            'with' => 'itemProperties.valueTexts,itemCrossSelling',
        ]);
    }

    /**
     * @return array
     */
    public function findAll()
    {
        $languageHelper = new LanguageHelper();

        return iterator_to_array($this->client->getIterator('items', [
            'lang' => $languageHelper->getLanguagesQueryString(),
            'with' => 'itemProperties.valueTexts,itemCrossSelling',
        ]));
    }

    /**
     * @param $startTimestamp
     * @param $endTimestamp
     *
     * @return array
     */
    public function findChanged(DateTimeImmutable $startTimestamp, DateTimeImmutable $endTimestamp)
    {
        $start = $startTimestamp->format(DATE_W3C);
        $end = $endTimestamp->format(DATE_W3C);

        $languageHelper = new LanguageHelper();

        return iterator_to_array($this->client->getIterator('items', [
            'lang' => $languageHelper->getLanguagesQueryString(),
            'updatedBetween' => $start . ',' . $end,
            'with' => 'itemProperties.valueTexts,itemCrossSelling',
        ]));
    }
}
