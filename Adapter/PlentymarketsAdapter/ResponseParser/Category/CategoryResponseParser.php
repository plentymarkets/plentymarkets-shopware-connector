<?php

namespace PlentymarketsAdapter\ResponseParser\Category;

use Exception;
use PlentymarketsAdapter\Helper\MediaCategoryHelper;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\Media\MediaResponseParserInterface;
use Psr\Log\LoggerInterface;
use SystemConnector\ConfigService\ConfigServiceInterface;
use SystemConnector\IdentityService\IdentityServiceInterface;
use SystemConnector\IdentityService\Struct\Identity;
use SystemConnector\TransferObject\Category\Category;
use SystemConnector\TransferObject\Language\Language;
use SystemConnector\TransferObject\Shop\Shop;
use SystemConnector\ValueObject\Attribute\Attribute;
use SystemConnector\ValueObject\Translation\Translation;

class CategoryResponseParser implements CategoryResponseParserInterface
{
    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * @var ConfigServiceInterface
     */
    private $configService;

    /**
     * @var MediaResponseParserInterface
     */
    private $mediaResponseParser;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        IdentityServiceInterface $identityService,
        ConfigServiceInterface $configService,
        MediaResponseParserInterface $mediaResponseParser,
        LoggerInterface $logger
    ) {
        $this->identityService = $identityService;
        $this->configService = $configService;
        $this->mediaResponseParser = $mediaResponseParser;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(array $entry): array
    {
        if (empty($entry['details'])) {
            $this->logger->warning('category without details');

            return [];
        }

        if ($entry['right'] !== 'all') {
            $this->logger->warning('unsupported category rights');

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
                $this->logger->warning('parent category was not found', ['category' => $categoryIdentity->getObjectIdentifier()]);

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
            $isDefaultPlentyId = $key === 0;
            $isPlentyIdEnabled = in_array($entry['details'][$key]['plentyId'], array_column($entry['clients'], 'plentyId'), true);
            if (!$isPlentyIdEnabled && !$isDefaultPlentyId) {
                unset($entry['details'][$key]);

                continue;
            }
        }

        $validDetails = array_values(array_filter($entry['details'], function (array $detail) {
            if (empty($detail['plentyId'])) {
                return false;
            }

            $identity = $this->getShopIdentity($detail['plentyId']);

            return !(null === $identity);
        }));

        if (empty($validDetails)) {
            $validDetails = $entry['details'];
        }

        $result = [];

        $category = new Category();
        $category->setIdentifier($categoryIdentity->getObjectIdentifier());
        $category->setParentIdentifier($parentCategoryIdentifier);
        $category->setShopIdentifiers($shopIdentifiers);
        $category->setImageIdentifiers($this->getImages($validDetails['0'], $result));
        $category->setName($validDetails['0']['name']);
        $category->setActive(true);
        $category->setPosition((int) $validDetails['0']['position']);
        $category->setDescription($validDetails['0']['shortDescription']);
        $category->setLongDescription($validDetails['0']['description']);
        $category->setMetaTitle($validDetails['0']['metaTitle']);
        $category->setMetaDescription($validDetails['0']['metaDescription']);
        $category->setMetaKeywords($validDetails['0']['metaKeywords']);
        $category->setMetaRobots($this->getMetaRobots($validDetails['0']['metaRobots']));
        $category->setTranslations($this->getTranslations($validDetails, $result));
        $category->setAttributes($this->getAttributes($validDetails));

        return array_merge($result, [$category]);
    }

    /**
     * @param int $plentyId
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

        $isMappedIdentity = $this->identityService->isMappedIdentity(
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
     * @param array $result
     */
    private function getImages(array $detail, &$result): array
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
            try {
                $media = $this->mediaResponseParser->parse([
                    'mediaCategory' => MediaCategoryHelper::CATEGORY,
                    'id' => sha1(json_encode($image)),
                    'link' => $this->getBaseUrl() . 'documents/' . $image,
                    'name' => $detail['name'],
                    'alternateName' => $detail['name'],
                ]);

                $result[$media->getIdentifier()] = $media;

                $imageIdentifiers[] = $media->getIdentifier();
            } catch (Exception $exception) {
                $this->logger->notice('error while processing category image', ['name' => $detail['name']]);
            }
        }

        return $imageIdentifiers;
    }

    private function getBaseUrl(): string
    {
        $parts = parse_url($this->configService->get('rest_url'));

        return sprintf('%s://%s/', $parts['scheme'], $parts['host']);
    }

    /**
     * @param string $metaRobots
     */
    private function getMetaRobots($metaRobots): string
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
     * @param array $result
     *
     * @return Translation[]
     */
    private function getTranslations(array $details, &$result): array
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

            $translations[] = Translation::fromArray([
                'languageIdentifier' => $languageIdentifier->getObjectIdentifier(),
                'property' => 'plentyId',
                'value' => $detail['plentyId'],
            ]);
        }

        return $translations;
    }

    /**
     * @return Attribute[]
     */
    private function getAttributes(array $details): array
    {
        $attributes = [];

        $attributes[] = $this->getSecondCategoryDescriptionAsAttribute($details);

        return $attributes;
    }

    /**
     * @param array $categoryDetails
     */
    private function getSecondCategoryDescriptionAsAttribute($categoryDetails): Attribute
    {
        $translations = [];

        foreach ($categoryDetails as $categoryDetail) {
            $languageIdentifier = $this->identityService->findOneBy([
                'adapterIdentifier' => $categoryDetail['lang'],
                'adapterName' => PlentymarketsAdapter::NAME,
                'objectType' => Language::TYPE,
            ]);

            if (null === $languageIdentifier) {
                continue;
            }

            $translations[] = Translation::fromArray([
                'languageIdentifier' => $languageIdentifier->getObjectIdentifier(),
                'property' => 'value',
                'value' => $categoryDetail['description2'],
            ]);
        }

        $attribute = new Attribute();
        $attribute->setKey('secondCategoryDescription');
        $attribute->setValue((string) $categoryDetails[0]['description2']);
        $attribute->setTranslations($translations);

        return $attribute;
    }
}
