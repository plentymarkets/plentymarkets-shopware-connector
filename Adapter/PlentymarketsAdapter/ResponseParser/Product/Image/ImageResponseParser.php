<?php

namespace PlentymarketsAdapter\ResponseParser\Product\Image;

use Exception;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\Language\Language;
use PlentyConnector\Connector\TransferObject\Product\Image\Image;
use PlentyConnector\Connector\TransferObject\Shop\Shop;
use PlentyConnector\Connector\ValueObject\Translation\Translation;
use PlentymarketsAdapter\Helper\MediaCategoryHelper;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\Media\MediaResponseParserInterface;
use Psr\Log\LoggerInterface;

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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ImageResponseParser constructor.
     *
     * @param IdentityServiceInterface     $identityService
     * @param MediaResponseParserInterface $mediaResponseParser
     * @param LoggerInterface              $logger
     */
    public function __construct(
        IdentityServiceInterface $identityService,
        MediaResponseParserInterface $mediaResponseParser,
        LoggerInterface $logger
    ) {
        $this->identityService = $identityService;
        $this->mediaResponseParser = $mediaResponseParser;
        $this->logger = $logger;
    }

    /**
     * @param array $entry
     * @param array $texts
     * @param array $result
     *
     * @return null|Image
     */
    public function parseImage(array $entry, array $texts, array &$result)
    {
        try {
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
        } catch (Exception $exception) {
            $this->logger->notice('error when parsing product image', [
                'name' => $entry['names'][0]['name'],
                'url' => $entry['url'],
            ]);
        }

        return null;
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
