<?php

namespace PlentymarketsAdapter\ReadApi;

use DateTimeImmutable;
use PlentymarketsAdapter\Client\Client;
use PlentymarketsAdapter\Client\Iterator\Iterator;
use PlentymarketsAdapter\Helper\LanguageHelperInterface;
use PlentymarketsAdapter\Helper\VariationHelperInterface;
use PlentymarketsAdapter\ReadApi\Availability as AvailabilityApi;
use PlentymarketsAdapter\ReadApi\Item\Attribute as AttributeApi;
use PlentymarketsAdapter\ReadApi\Item\Barcode as BarcodeApi;
use PlentymarketsAdapter\ReadApi\Item\Property\Group as PropertyGroupApi;
use PlentymarketsAdapter\ReadApi\Item\Property\Name as PropertyNameApi;
use PlentymarketsAdapter\ReadApi\Item\Variation as VariationApi;

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
     * @var VariationHelperInterface
     */
    private $variationHelper;

    private $attributes = [];

    private $barcodes = [];

    private $propertyGroups = [];

    private $propertyNames = [];

    private $availabilities = [];

    /**
     * @var array
     */
    private static $includes = [
        'itemProperties.valueTexts',
        'itemCrossSelling',
        'itemImages',
        'itemShippingProfiles',
    ];

    public function __construct(
        Client $client,
        LanguageHelperInterface $languageHelper,
        VariationHelperInterface $variationHelper
    ) {
        parent::__construct($client);

        $this->languageHelper = $languageHelper;
        $this->variationHelper = $variationHelper;

        $this->itemsVariationsApi = new VariationApi($client);
        $this->itemsPropertyGroupsApi = new PropertyGroupApi($client);
        $this->itemsPropertyNamesApi = new PropertyNameApi($client);
        $this->availabilitiesApi = new AvailabilityApi($client);
        $this->itemAttributesApi = new AttributeApi($client);
        $this->itemBarcodeApi = new BarcodeApi($client);
    }

    /**
     * @param int $productId
     *
     * @return array
     */
    public function findOne($productId)
    {
        $result = $this->client->request('GET', 'items/' . $productId, [
            'lang' => $this->languageHelper->getLanguagesQueryString(),
            'with' => implode(',', self::$includes),
        ]);

        if (empty($result)) {
            return $result;
        }

        $result['variations'] = $this->itemsVariationsApi->findBy([
            'itemId' => $result['id'],
            'plentyId' => implode(',', $this->variationHelper->getMappedPlentyClientIds()),
        ]);

        return $result;
    }

    /**
     * @return Iterator
     */
    public function findAll()
    {
        return $this->client->getIterator('items', [
            'lang' => $this->languageHelper->getLanguagesQueryString(),
            'with' => implode(',', self::$includes),
        ], function (array $elements) {
            $this->addAdditionalData($elements);

            return $elements;
        });
    }

    /**
     * @param DateTimeImmutable $startTimestamp
     * @param DateTimeImmutable $endTimestamp
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
            'with' => implode(',', self::$includes),
        ], function (array $elements) {
            $this->addAdditionalData($elements);

            return $elements;
        });
    }

    /**
     * @param DateTimeImmutable $startTimestamp
     * @param DateTimeImmutable $endTimestamp
     *
     * @return Iterator
     */
    public function findChangedVariations(DateTimeImmutable $startTimestamp, DateTimeImmutable $endTimestamp)
    {
        $start = $startTimestamp->format(DATE_W3C);
        $end = $endTimestamp->format(DATE_W3C);

        return $this->client->getIterator('items', [
            'lang' => $this->languageHelper->getLanguagesQueryString(),
            'variationUpdatedBetween' => $start . ',' . $end,
            'with' => implode(',', self::$includes),
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

        $variations = $this->itemsVariationsApi->findBy([
            'itemId' => implode(',', $items),
            'plentyId' => implode(',', $this->variationHelper->getMappedPlentyClientIds()),
        ]);

        foreach ($elements as $key => $element) {
            $elements[$key]['variations'] = array_filter($variations, function (array $variation) use ($element) {
                return $element['id'] === $variation['itemId'];
            });

            $elements[$key]['__attributes'] = $this->getAttributes();
            $elements[$key]['__barcodes'] = $this->getBarcodes();
            $elements[$key]['__propertyGroups'] = $this->getPropertyGroups();
            $elements[$key]['__properties'] = $this->getProperties();
            $elements[$key]['__availabilities'] = $this->getAvailabilities();
        }
    }

    private function getAttributes()
    {
        if (empty($this->attributes)) {
            $this->attributes = $this->itemAttributesApi->findAll();
        }

        return $this->attributes;
    }

    private function getBarcodes()
    {
        if (empty($this->barcodes)) {
            $this->barcodes = $this->itemBarcodeApi->findAll();
        }

        return $this->barcodes;
    }

    private function getPropertyGroups()
    {
        if (empty($this->propertyGroups)) {
            $this->propertyGroups = $this->itemsPropertyGroupsApi->findAll();
        }

        return $this->propertyGroups;
    }

    private function getProperties()
    {
        if (empty($this->properties)) {
            $this->properties = $this->itemsPropertyNamesApi->findAll();
        }

        return $this->properties;
    }

    private function getAvailabilities()
    {
        if (empty($this->availabilities)) {
            $this->availabilities = $this->availabilitiesApi->findAll();
        }

        return $this->availabilities;
    }
}
