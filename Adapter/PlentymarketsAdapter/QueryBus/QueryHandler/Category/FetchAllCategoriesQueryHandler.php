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
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\ResponseParserInterface;

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
     * @var ResponseParserInterface
     */
    private $categoryResponseParser;

    /**
     * @var ResponseParserInterface
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
     * FetchAllCategoriesQueryHandler constructor.
     *
     * @param ClientInterface $client
     * @param ResponseParserInterface $categoryResponseParser
     * @param ResponseParserInterface $mediaResponseParser
     * @param ConfigService $config
     * @param IdentityServiceInterface $identityService
     */
    public function __construct(
        ClientInterface $client,
        ResponseParserInterface $categoryResponseParser,
        ResponseParserInterface $mediaResponseParser,
        ConfigService $config,
        IdentityServiceInterface $identityService
    ) {
        $this->client = $client;
        $this->categoryResponseParser = $categoryResponseParser;
        $this->mediaResponseParser = $mediaResponseParser;
        $this->config = $config;
        $this->identityService = $identityService;
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
        ]);

        $elements = array_filter($elements, function ($element) {
            return $element['type'] === 'item' && $element['right'] === 'all';
        });

        $parts = parse_url($this->config->get('rest_url'));
        $baseUrl = sprintf('https://%s/', $parts['host']);

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

            foreach ($categoriesGrouped as $plentyId => $group) {
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

                $name = $group['0']['name'];
                $description = $group['0']['description'];
                $longDescription = $group['0']['description2'];
                $position = $group['0']['position'];

                $metaTitle = $group['0']['metaTitle'];
                $metaDescription = $group['0']['metaDescription'];
                $metaKeywords = $group['0']['metaKeywords'];
                $metaRobots = $group['0']['metaRobots'];

                $translations = [];
                $imageIdentifiers = [];

                foreach ($group as $detail) {
                    $languageIdentifier = $this->identityService->findOneOrThrow(
                        $detail['lang'],
                        PlentymarketsAdapter::NAME,
                        Language::TYPE
                    );

                    if (!empty($detail['image'])) {
                        $result[] = $media = $this->mediaResponseParser->parse([
                            'link' => $baseUrl . 'documents/' . $detail['image'],
                            'name' => $detail['name'],
                            'alternateName' => $detail['name'],
                        ]);

                        $imageIdentifiers[] = $media->getIdentifier();
                    }

                    if (!empty($detail['image2'])) {
                        $result[] = $media = $this->mediaResponseParser->parse([
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
}
