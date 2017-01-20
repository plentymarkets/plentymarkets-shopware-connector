<?php

namespace PlentymarketsAdapter\QueryBus\QueryHandler\Category;

use PlentyConnector\Connector\ConfigService\ConfigService;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\QueryBus\Query\Category\FetchAllCategoriesQuery;
use PlentyConnector\Connector\QueryBus\Query\QueryInterface;
use PlentyConnector\Connector\QueryBus\QueryHandler\QueryHandlerInterface;
use PlentyConnector\Connector\TransferObject\Category\Category;
use PlentyConnector\Connector\TransferObject\Language\Language;
use PlentyConnector\Connector\TransferObject\Shop\Shop;
use PlentyConnector\Connector\ValueObject\Translation\Translation;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\Helper\LanguageHelper;
use PlentymarketsAdapter\Helper\MediaCategoryHelper;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\Category\CategoryResponseParserInterface;
use PlentymarketsAdapter\ResponseParser\Media\MediaResponseParserInterface;

/**
 * Class FetchAllCategoriesQueryHandler
 */
class FetchAllCategoriesQueryHandler implements QueryHandlerInterface
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var CategoryResponseParserInterface
     */
    private $categoryResponseParser;

    /**
     * @var MediaResponseParserInterface
     */
    private $mediaResponseParser;

    /**
     * @var ConfigService
     */
    private $config;

    /**
     * @var IdentityServiceInterface
     */
    private $identityService;
    /**
     * @var LanguageHelper
     */
    private $languageHelper;

    /**
     * FetchAllCategoriesQueryHandler constructor.
     *
     * @param ClientInterface $client
     * @param CategoryResponseParserInterface $categoryResponseParser
     * @param MediaResponseParserInterface $mediaResponseParser
     * @param ConfigService $config
     * @param IdentityServiceInterface $identityService
     * @param LanguageHelper $languageHelper
     */
    public function __construct(
        ClientInterface $client,
        CategoryResponseParserInterface $categoryResponseParser,
        MediaResponseParserInterface $mediaResponseParser,
        ConfigService $config,
        IdentityServiceInterface $identityService,
        LanguageHelper $languageHelper
    ) {
        $this->client = $client;
        $this->categoryResponseParser = $categoryResponseParser;
        $this->mediaResponseParser = $mediaResponseParser;
        $this->config = $config;
        $this->identityService = $identityService;
        $this->languageHelper = $languageHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query)
    {
        return $query instanceof FetchAllCategoriesQuery &&
            $query->getAdapterName() === PlentymarketsAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $elements = $this->client->request('GET', 'categories', [
            'with' => 'clients,details',
            'lang' => implode(',', array_column($this->languageHelper->getLanguages(), 'id'))
        ]);

        $elements = array_filter($elements, function ($element) {
            return $element['type'] === 'item' && $element['right'] === 'all';
        });

        $baseUrl = $this->getBaseUrl();

        $result = [];

        array_walk($elements, function (array $element) use (&$result, $baseUrl) {
            if (empty($element['details'])) {
                return;
            }

            $categoriesGrouped = [];
            if (is_array($element['details'])) {
                foreach ($element['details'] as $detail) {
                    $categoriesGrouped[$detail['plentyId']][] = $detail;
                }
            }

            foreach ($categoriesGrouped as $plentyId => $details) {
                $categoryIdentifier = $this->identityService->findOneOrCreate(
                    (string)($plentyId . '-' . $element['id']),
                    PlentymarketsAdapter::NAME,
                    Category::TYPE
                );

                if (null !== $element['parentCategoryId']) {
                    $parentIdentity = $this->identityService->findOneOrCreate(
                        (string)($plentyId . '-' . $element['parentCategoryId']),
                        PlentymarketsAdapter::NAME,
                        Category::TYPE
                    );

                    $parentCategoryIdentifier = $parentIdentity->getObjectIdentifier();
                } else {
                    $parentCategoryIdentifier = null;
                }

                $shppIdentifier = $this->identityService->findOneOrCreate(
                    (string)$plentyId,
                    PlentymarketsAdapter::NAME,
                    Shop::TYPE
                );

                $name = $details['0']['name'];
                $description = $details['0']['description'];
                $longDescription = $details['0']['description2'];
                $position = $details['0']['position'];

                $metaTitle = $details['0']['metaTitle'];
                $metaDescription = $details['0']['metaDescription'];
                $metaKeywords = $details['0']['metaKeywords'];
                $metaRobots = $details['0']['metaRobots'];

                $translations = [];
                $imageIdentifiers = [];

                foreach ($details as $detail) {
                    $languageIdentifier = $this->identityService->findOneOrThrow(
                        $detail['lang'],
                        PlentymarketsAdapter::NAME,
                        Language::TYPE
                    );

                    if (!empty($detail['image'])) {
                        $result[] = $media = $this->mediaResponseParser->parse([
                            'mediaCategory' => MediaCategoryHelper::CATEGORY,
                            'link' => $baseUrl . 'documents/' . $detail['image'],
                            'name' => $detail['name'],
                            'alternateName' => $detail['name'],
                        ]);

                        $imageIdentifiers[] = $media->getIdentifier();
                    }

                    if (!empty($detail['image2'])) {
                        $result[] = $media = $this->mediaResponseParser->parse([
                            'mediaCategory' => MediaCategoryHelper::CATEGORY,
                            'link' => $baseUrl . 'documents/' . $detail['image2'],
                            'name' => $detail['name'],
                            'alternateName' => $detail['name'],
                        ]);

                        $imageIdentifiers[] = $media->getIdentifier();
                    }

                    $translations[] = Translation::fromArray([
                        'languageIdentifier' => $languageIdentifier->getObjectIdentifier(),
                        'property' => 'name',
                        'value' => $detail['name'],
                    ]);

                    $translations[] = Translation::fromArray([
                        'languageIdentifier' => $languageIdentifier->getObjectIdentifier(),
                        'property' => 'description',
                        'value' => $detail['description'],
                    ]);

                    $translations[] = Translation::fromArray([
                        'languageIdentifier' => $languageIdentifier->getObjectIdentifier(),
                        'property' => 'longDescription',
                        'value' => $detail['description2'],
                    ]);

                    $translations[] = Translation::fromArray([
                        'languageIdentifier' => $languageIdentifier->getObjectIdentifier(),
                        'property' => 'metaTitle',
                        'value' => $detail['metaTitle'],
                    ]);

                    $translations[] = Translation::fromArray([
                        'languageIdentifier' => $languageIdentifier->getObjectIdentifier(),
                        'property' => 'metaDescription',
                        'value' => $detail['metaDescription'],
                    ]);

                    $translations[] = Translation::fromArray([
                        'languageIdentifier' => $languageIdentifier->getObjectIdentifier(),
                        'property' => 'metaKeywords',
                        'value' => $detail['metaKeywords'],
                    ]);

                    $translations[] = Translation::fromArray([
                        'languageIdentifier' => $languageIdentifier->getObjectIdentifier(),
                        'property' => 'metaRobots',
                        'value' => $detail['metaRobots'],
                    ]);
                }

                $result[] = Category::fromArray([
                    'identifier' => $categoryIdentifier->getObjectIdentifier(),
                    'name' => $name,
                    'parentIdentifier' => $parentCategoryIdentifier,
                    'shopIdentifier' => $shppIdentifier->getObjectIdentifier(),
                    'imageIdentifiers' => $imageIdentifiers,
                    'position' => $position,
                    'description' => $description,
                    'longDescription' => $longDescription,
                    'metaTitle' => $metaTitle,
                    'metaDescription' => $metaDescription,
                    'metaKeywords' => $metaKeywords,
                    'metaRobots' => $metaRobots,
                    'translations' => $translations,
                    'attributes' => [],
                ]);
            }
        });

        return array_filter($result);
    }

    /**
     * @return string
     */
    private function getBaseUrl()
    {
        $parts = parse_url($this->config->get('rest_url'));

        return sprintf('https://%s/', $parts['host']);
    }
}
