<?php

namespace PlentymarketsAdapter\ResponseParser\Category;

use PlentyConnector\Connector\ConfigService\ConfigServiceInterface;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\Category\Category;
use PlentyConnector\Connector\TransferObject\Language\Language;
use PlentyConnector\Connector\TransferObject\Shop\Shop;
use PlentyConnector\Connector\ValueObject\Translation\Translation;
use PlentymarketsAdapter\Helper\MediaCategoryHelper;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\Media\MediaResponseParserInterface;
use Psr\Log\LoggerInterface;

/**
 * Class CategoryResponseParser
 */
class CategoryResponseParser implements CategoryResponseParserInterface
{
    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * @var ConfigServiceInterface
     */
    private $config;

    /**
     * @var MediaResponseParserInterface
     */
    private $mediaResponseParser;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * CategoryResponseParser constructor.
     *
     * @param IdentityServiceInterface     $identityService
     * @param ConfigServiceInterface       $config
     * @param MediaResponseParserInterface $mediaResponseParser
     * @param LoggerInterface              $logger
     */
    public function __construct(
        IdentityServiceInterface $identityService,
        ConfigServiceInterface $config,
        MediaResponseParserInterface $mediaResponseParser,
        LoggerInterface $logger
    ) {
        $this->identityService = $identityService;
        $this->config = $config;
        $this->mediaResponseParser = $mediaResponseParser;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(array $entry)
    {
        if (empty($entry['details'])) {
            return [];
        }

        $categoryIdentifier = $this->identityService->findOneOrCreate(
            (string) $entry['id'],
            PlentymarketsAdapter::NAME,
            Category::TYPE
        );

        if (null !== $entry['parentCategoryId']) {
            $parentIdentity = $this->identityService->findOneOrCreate(
                (string) $entry['parentCategoryId'],
                PlentymarketsAdapter::NAME,
                Category::TYPE
            );

            $parentCategoryIdentifier = $parentIdentity->getObjectIdentifier();
        } else {
            $parentCategoryIdentifier = null;
        }

        $shopIdentifiers = [];
        foreach ($entry['clients'] as $client) {
            $identity = $this->identityService->findOneOrCreate(
                (string) $client['plentyId'],
                PlentymarketsAdapter::NAME,
                Shop::TYPE
            );

            if (null === $identity) {
                $this->logger->notice('shop not found');

                continue;
            }

            $shopIdentifiers[] = $identity->getObjectIdentifier();
        }

        $result = [];

        $result[] = Category::fromArray([
            'identifier' => $categoryIdentifier->getObjectIdentifier(),
            'name' => $entry['details']['0']['name'],
            'parentIdentifier' => $parentCategoryIdentifier,
            'shopIdentifiers' => $shopIdentifiers,
            'imageIdentifiers' => $this->getImages($entry['details']['0'], $result),
            'position' => $entry['details']['0']['position'],
            'description' => $entry['details']['0']['shortDescription'],
            'longDescription' => $entry['details']['0']['description'],
            'metaTitle' => $entry['details']['0']['metaTitle'],
            'metaDescription' => $entry['details']['0']['metaDescription'],
            'metaKeywords' => $entry['details']['0']['metaKeywords'],
            'metaRobots' => $this->getMetaRobots($entry['details']['0']['metaRobots']),
            'translations' => $this->getTranslations($entry['details'], $result),
            'attributes' => [],
        ]);

        return $result;
    }

    /**
     * @param array $detail
     * @param $result
     *
     * @return array
     */
    private function getImages(array $detail, &$result)
    {
        $imageIdentifiers = [];

        if (!empty($detail['image'])) {
            $result[] = $media = $this->mediaResponseParser->parse([
                'mediaCategory' => MediaCategoryHelper::CATEGORY,
                'link' => $this->getBaseUrl() . 'documents/' . $detail['image'],
                'name' => $detail['name'],
                'alternateName' => $detail['name'],
            ]);

            $imageIdentifiers[] = $media->getIdentifier();
        }

        if (!empty($detail['image2'])) {
            $result[] = $media = $this->mediaResponseParser->parse([
                'mediaCategory' => MediaCategoryHelper::CATEGORY,
                'link' => $this->getBaseUrl() . 'documents/' . $detail['image2'],
                'name' => $detail['name'],
                'alternateName' => $detail['name'],
            ]);

            $imageIdentifiers[] = $media->getIdentifier();
        }

        return $imageIdentifiers;
    }

    /**
     * @return string
     */
    private function getBaseUrl()
    {
        $parts = parse_url($this->config->get('rest_url'));

        return sprintf('https://%s/', $parts['host']);
    }

    /**
     * @param $metaRobots
     *
     * @return string
     */
    private function getMetaRobots($metaRobots)
    {
        $robotsMap = [
            'ALL' => 'INDEX, FOLLOW',
            'INDEX' => 'INDEX, FOLLOW',
            'NOFOLLOW' => 'INDEX, NOFOLLOW',
            'NOINDEX' => 'NOINDEX, FOLLOW',
            'NOINDEX, NOFOLLOW' => 'NOINDEX, NOFOLLOW',
        ];

        if (array_key_exists(strtoupper($metaRobots), $robotsMap)) {
            return $robotsMap[$metaRobots];
        }

        return 'INDEX, FOLLOW';
    }

    /**
     * @param array $details
     * @param $result
     *
     * @return Translation[]
     */
    private function getTranslations(array $details, &$result)
    {
        $translations = [];

        foreach ($details as $detail) {
            $languageIdentifier = $this->identityService->findOneBy([
                'adapterIdentifier' => $detail['lang'],
                'adapterName' => PlentymarketsAdapter::NAME,
                'objectType' => Language::TYPE,
            ]);

            if (null === $languageIdentifier) {
                continue;
            }

            $translations[] = Translation::fromArray([
                'languageIdentifier' => $languageIdentifier->getObjectIdentifier(),
                'property' => 'imageIdentifiers',
                'value' => $this->getImages($detail, $result),
            ]);

            $translations[] = Translation::fromArray([
                'languageIdentifier' => $languageIdentifier->getObjectIdentifier(),
                'property' => 'name',
                'value' => $detail['name'],
            ]);

            $translations[] = Translation::fromArray([
                'languageIdentifier' => $languageIdentifier->getObjectIdentifier(),
                'property' => 'description',
                'value' => $detail['shortDescription'],
            ]);

            $translations[] = Translation::fromArray([
                'languageIdentifier' => $languageIdentifier->getObjectIdentifier(),
                'property' => 'longDescription',
                'value' => $detail['description'],
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
                'value' => $this->getMetaRobots($detail['metaRobots']),
            ]);
        }

        return $translations;
    }
}
