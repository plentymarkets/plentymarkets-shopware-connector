<?php

namespace PlentymarketsAdapter\ResponseParser\Product\Image;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\Language\Language;
use PlentyConnector\Connector\TransferObject\Product\Image\Image;
use PlentyConnector\Connector\TransferObject\Shop\Shop;
use PlentyConnector\Connector\ValueObject\Translation\Translation;
use PlentymarketsAdapter\Helper\MediaCategoryHelper;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\Media\MediaResponseParserInterface;

/**
 * Class ImageResponseParser
 */
class ImageResponseParser implements ImageResponseParserInterface
{
    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * @var MediaResponseParserInterface
     */
    private $mediaResponseParser;

    /**
     * ImageResponseParser constructor.
     *
     * @param IdentityServiceInterface     $identityService
     * @param MediaResponseParserInterface $mediaResponseParser
     */
    public function __construct(
        IdentityServiceInterface $identityService,
        MediaResponseParserInterface $mediaResponseParser
    ) {
        $this->identityService = $identityService;
        $this->mediaResponseParser = $mediaResponseParser;
    }

    /**
     * @param array $entry
     * @param array $texts
     * @param array $result
     *
     * @return Image
     */
    public function parseImage(array $entry, array $texts, array &$result)
    {
        if (!empty($entry['names'][0]['name'])) {
            $name = $entry['names'][0]['name'];
        } else {
            $name = $texts[0]['name1'];
        }

        $alternate = $name;
        if (!empty($entry['names'][0]['alternate'])) {
            $alternate = $entry['names'][0]['alternate'];
        }

        $media = $this->mediaResponseParser->parse([
            'mediaCategory' => MediaCategoryHelper::PRODUCT,
            'link' => $entry['url'],
            'name' => $name,
            'hash' => $entry['md5Checksum'],
            'alternateName' => $alternate,
            'translations' => $this->getMediaTranslations($entry, $texts),
        ]);

        if (null === $media) {
            return null;
        }

        $result[$media->getIdentifier()] = $media;

        $linkedShops = array_filter($entry['availabilities'], function (array $availabilitiy) {
            return $availabilitiy['type'] === 'mandant';
        });

        $shopIdentifiers = array_map(function ($shop) {
            $shopIdentity = $this->identityService->findOneBy([
                'adapterIdentifier' => (string) $shop['value'],
                'adapterName' => PlentymarketsAdapter::NAME,
                'objectType' => Shop::TYPE,
            ]);

            if (null === $shopIdentity) {
                return null;
            }

            return $shopIdentity->getObjectIdentifier();
        }, $linkedShops);

        $image = new Image();
        $image->setMediaIdentifier($media->getIdentifier());
        $image->setShopIdentifiers(array_filter($shopIdentifiers));
        $image->setPosition((int) $entry['position']);

        return $image;
    }

    /**
     * @param array $image
     * @param array $productTexts
     *
     * @return array
     */
    private function getMediaTranslations(array $image, array $productTexts)
    {
        $translations = [];

        foreach ($image['names'] as $text) {
            $languageIdentifier = $this->identityService->findOneBy([
                'adapterIdentifier' => $text['lang'],
                'adapterName' => PlentymarketsAdapter::NAME,
                'objectType' => Language::TYPE,
            ]);

            if (null === $languageIdentifier) {
                continue;
            }

            if (!empty($text['name'])) {
                $name = $text['name'];
            } else {
                $name = '';

                foreach ($productTexts as $productText) {
                    if ($text['lang'] === $productText['lang']) {
                        $name = $productText['name1'];
                    }
                }
            }

            $translations[] = Translation::fromArray([
                'languageIdentifier' => $languageIdentifier->getObjectIdentifier(),
                'property' => 'name',
                'value' => $name,
            ]);

            if (!empty($text['alternate'])) {
                $alternate = $text['alternate'];
            } else {
                $alternate = '';

                foreach ($productTexts as $productText) {
                    if ($text['lang'] === $productText['lang']) {
                        $alternate = $productText['name1'];
                    }
                }
            }

            $translations[] = Translation::fromArray([
                'languageIdentifier' => $languageIdentifier->getObjectIdentifier(),
                'property' => 'alternateName',
                'value' => $alternate,
            ]);
        }

        return $translations;
    }
}
