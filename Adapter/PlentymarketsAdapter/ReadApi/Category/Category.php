<?php

namespace PlentymarketsAdapter\ReadApi\Category;

use DateTimeImmutable;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\Helper\LanguageHelperInterface;
use PlentymarketsAdapter\ReadApi\ApiAbstract;

class Category extends ApiAbstract
{
    /**
     * @var LanguageHelperInterface
     */
    private $languageHelper;

    public function __construct(
        ClientInterface $client,
        LanguageHelperInterface $languageHelper
    ) {
        parent::__construct($client);

        $this->languageHelper = $languageHelper;
    }

    /**
     * @param int $categoryId
     *
     * @return array
     */
    public function findOne($categoryId): array
    {
        return $this->client->request('GET', 'categories/' . $categoryId, [
            'with' => 'details,clients',
            'type' => 'item',
            'lang' => $this->languageHelper->getLanguagesQueryString(),
        ]);
    }

    /**
     * @return array
     */
    public function findAll(): array
    {
        $elements = iterator_to_array($this->client->getIterator('categories', [
            'with' => 'details,clients',
            'type' => 'item',
            'lang' => $this->languageHelper->getLanguagesQueryString(),
        ]));

        $this->sortCategories($elements);

        return $elements;
    }

    /**
     * @param DateTimeImmutable $startTimestamp
     *
     * @return array
     */
    public function findChanged(DateTimeImmutable $startTimestamp): array
    {
        $elements = iterator_to_array($this->client->getIterator('categories', [
            'with' => 'details,clients',
            'type' => 'item',
            'updatedAt' => $startTimestamp->format(DATE_W3C),
            'lang' => $this->languageHelper->getLanguagesQueryString(),
        ]));

        $this->sortCategories($elements);

        return $elements;
    }

    /**
     * @param array $categories
     */
    private function sortCategories(array &$categories)
    {
        usort($categories, static function ($a, $b) {
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
