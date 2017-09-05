<?php

namespace PlentymarketsAdapter\ResponseParser\Category;

use PlentyConnector\Connector\ConfigService\ConfigServiceInterface;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\Category\Category;
use PlentyConnector\Connector\TransferObject\Language\Language;
use PlentyConnector\Connector\TransferObject\Shop\Shop;
use PlentyConnector\Connector\ValueObject\Identity\Identity;
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
            $this->logger->notice('category without details');

            return [];
        }

        if ($entry['right'] !== 'all') {
            $this->logger->notice('unsupported category rights');

            return [];
        }

        $categoryIdentity = $this->identityService->findOneOrCreate(
            (string) $entry['id'],
            PlentymarketsAdapter::NAME,
            Category::TYPE
        );

        if (null !== $entry['parentCategoryId']) {
            $parentIdentity = $this->identityService->findOneBy([
                'adapterIdentifier' => (string) $entry['parentCategoryId'],
                'adapterName' => PlentymarketsAdapter::NAME,
                'objectType' => Category::TYPE,
            ]);

            if (null === $parentIdentity) {
                $this->logger->notice('parent category was not found', ['category' => $categoryIdentity->getObjectIdentifier()]);

                return [];
            }

            $parentCategoryIdentifier = $parentIdentity->getObjectIdentifier();
        } else {
            $parentCategoryIdentifier = null;
        }

        if (empty($entry['clients'])) {
            return [];
        }

        $shopIdentifiers = [];
        foreach ($entry['clients'] as $client) {
            if (empty($client['plentyId'])) {
                continue;
            }

            $identity = $this->getShopIdentity($client['plentyId']);

            if (null === $identity) {
                continue;
            }

            $shopIdentifiers[] = $identity->getObjectIdentifier();
        }

        foreach ($entry['details'] as $key => $detail) {
            $entry['details'][$key]['shortDescription'] = $entry['details']['0']['shortDescription'];
            $entry['details'][$key]['position'] = $entry['details']['0']['position'];
            $entry['details'][$key]['image'] = $entry['details']['0']['image'];
            $entry['details'][$key]['imagePath'] = $entry['details']['0']['imagePath'];
            $entry['details'][$key]['image2'] = $entry['details']['0']['image2'];
            $entry['details'][$key]['image2Path'] = $entry['details']['0']['image2Path'];
        }

        $validDetails = array_values(array_filter($entry['details'], function (array $detail) {
            if (empty($detail['plentyId'])) {
                return false;
            }

            $identity = $this->getShopIdentity($detail['plentyId']);

            return !(null === $identity);
        }));

        if (empty($validDetails)) {
            $this->logger->notice('no valid category translation found, using default');

            $validDetails = $entry['details'];
        }

        $result = [];

        $result[] = Category::fromArray([
            'identifier' => $categoryIdentity->getObjectIdentifier(),
            'name' => $validDetails['0']['name'],
            'active' => true,
            'parentIdentifier' => $parentCategoryIdentifier,
            'shopIdentifiers' => $shopIdentifiers,
            'imageIdentifiers' => $this->getImages($validDetails['0'], $result),
            'position' => $validDetails['0']['position'],
            'description' => $validDetails['0']['shortDescription'],
            'longDescription' => $validDetails['0']['description'],
            'metaTitle' => $validDetails['0']['metaTitle'],
            'metaDescription' => $validDetails['0']['metaDescription'],
            'metaKeywords' => $validDetails['0']['metaKeywords'],
            'metaRobots' => $this->getMetaRobots($validDetails['0']['metaRobots']),
            'translations' => $this->getTranslations($validDetails, $result),
            'attributes' => [],
        ]);

        return $result;
    }

    /**
     * @param $plentyId
     *
     * @return null|Identity
     */
    private function getShopIdentity($plentyId)
    {
        $identity = $this->identityService->findOneBy([
            'adapterIdentifier' => (string) $plentyId,
            'adapterName' => PlentymarketsAdapter::NAME,
            'objectType' => Shop::TYPE,
        ]);

        if (null === $identity) {
            $this->logger->notice('shop not found', ['shop' => $plentyId]);

            return null;
        }

        $isMappedIdentity = $this->identityService->isMapppedIdentity(
            $identity->getObjectIdentifier(),
            $identity->getObjectType(),
            $identity->getAdapterName()
        );

        if (!$isMappedIdentity) {
            return null;
        }

        return $identity;
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

        $images = [];

        if (!empty($detail['imagePath'])) {
            $images[] = $detail['imagePath'];
        }

        if (!empty($detail['image2Path'])) {
            $images[] = $detail['image2Path'];
        }

        foreach ($images as $image) {
            $media = $this->mediaResponseParser->parse([
                'mediaCategory' => MediaCategoryHelper::CATEGORY,
                'link' => $this->getBaseUrl() . 'documents/' . $image,
                'name' => $detail['name'],
                'alternateName' => $detail['name'],
            ]);

            if (null !== $media) {
                $result[] = $media;

                $imageIdentifiers[] = $media->getIdentifier();
            }
        }

        return $imageIdentifiers;
    }

    /**
     * @return string
     */
    private function getBaseUrl()
    {
        $parts = parse_url($this->config->get('rest_url'));

        return sprintf('%s://%s/', $parts['scheme'], $parts['host']);
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
