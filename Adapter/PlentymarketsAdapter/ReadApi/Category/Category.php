<?php

namespace PlentymarketsAdapter\ReadApi\Category;

use DateTimeImmutable;
use PlentymarketsAdapter\Helper\LanguageHelper;
use PlentymarketsAdapter\ReadApi\ApiAbstract;

/**
 * Class Item
 */
class Category extends ApiAbstract
{
    /**
     * @param $categoryId
     *
     * @return array
     */
    public function findOne($categoryId)
    {
        $languageHelper = new LanguageHelper();

        return $this->client->request('GET', 'categories/' . $categoryId, [
            'with' => 'details,clients',
            'type' => 'item',
            'lang' => $languageHelper->getLanguagesQueryString(),
        ]);
    }

    /**
     * @return array
     */
    public function findAll()
    {
        $languageHelper = new LanguageHelper();

        $elements = iterator_to_array($this->client->getIterator('categories', [
            'with' => 'details,clients',
            'type' => 'item',
            'lang' => $languageHelper->getLanguagesQueryString(),
        ]));

        $this->sortCategories($elements);

        return $elements;
    }

    /**
     * @param DateTimeImmutable $startTimestamp
     * @param DateTimeImmutable $endTimestamp
     *
     * @return array
     */
    public function findChanged(DateTimeImmutable $startTimestamp, DateTimeImmutable $endTimestamp)
    {
        $languageHelper = new LanguageHelper();

        $elements = iterator_to_array($this->client->getIterator('categories', [
            'with' => 'details,clients',
            'type' => 'item',
            'updatedAt' => $startTimestamp->format(DATE_W3C),
            'lang' => $languageHelper->getLanguagesQueryString(),
        ]));

        $this->sortCategories($elements);

        return $elements;
    }

    /**
     * @param array $categories
     */
    private function sortCategories(array &$categories)
    {
        usort($categories, function ($a, $b) {
            if (!isset($a['level'], $b['level'])) {
                return 0;
            }

            if ((int) $a['level'] === (int) $b['level']) {
                return 0;
            }

            if ((int) $a['level'] < (int) $b['level']) {
                return -1;
            }

            return 1;
        });
    }
}
