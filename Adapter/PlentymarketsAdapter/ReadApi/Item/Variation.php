<?php

namespace PlentymarketsAdapter\ReadApi\Item;

use DateTimeImmutable;
use PlentymarketsAdapter\Helper\LanguageHelperInterface;
use PlentymarketsAdapter\ReadApi\ApiAbstract;
use PlentymarketsAdapter\Client\Iterator\Iterator;
use PlentymarketsAdapter\Client\Client;

class Variation extends ApiAbstract
{
    /**
     * @var LanguageHelperInterface
     */
    private $languageHelper;

    private $includes = 'variationClients,variationSalesPrices,variationCategories,variationDefaultCategory,unit,variationAttributeValues,variationBarcodes,images,stock,variationProperties';

    public function __construct(
        Client $client,
        LanguageHelperInterface $languageHelper
    ) {
        parent::__construct($client);
        $this->languageHelper = $languageHelper;
    }

    /**
     * @param array $criteria
     *
     * @return array
     */
    public function findBy(array $criteria)
    {
        $params = array_merge($criteria, [
            'with' => $this->includes,
        ]);

        return iterator_to_array($this->client->getIterator('items/variations', $params));
    }

    /**
     * @param DateTimeImmutable $startTimestamp
     * @param DateTimeImmutable $endTimestamp
     *
     * @return Iterator
     */
    public function findChangedVariation(DateTimeImmutable $startTimestamp, DateTimeImmutable $endTimestamp)
    {
        $start = $startTimestamp->format(DATE_W3C);
        $end = $endTimestamp->format(DATE_W3C);

        return $this->client->getIterator('items/variations', [
            'lang' => $this->languageHelper->getLanguagesQueryString(),
            'variationUpdatedBetween' => $start . ',' . $end,
            'with' => $this->includes,
        ]);
    }
}
