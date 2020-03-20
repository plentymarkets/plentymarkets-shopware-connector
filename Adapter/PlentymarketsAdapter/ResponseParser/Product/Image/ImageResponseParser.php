<?php

namespace PlentymarketsAdapter\ResponseParser\Product\Image;

use Exception;
use PlentymarketsAdapter\Helper\MediaCategoryHelper;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ResponseParser\Media\MediaResponseParserInterface;
use Psr\Log\LoggerInterface;
use SystemConnector\IdentityService\IdentityServiceInterface;
use SystemConnector\TransferObject\Language\Language;
use SystemConnector\TransferObject\Product\Image\Image;
use SystemConnector\TransferObject\Shop\Shop;
use SystemConnector\ValueObject\Translation\Translation;

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
                'id' => $entry['id'],
                'link' => $entry['url'],
                'filename' => $entry['cleanImageName'],
                'name' => $name,
                'hash' => $entry['md5Checksum'],
                'alternateName' => $alternate,
                'translations' => $this->getMediaTranslations($entry, $texts),
            ]);

            $result[$media->getIdentifier()] = $media;

            $linkedShops = array_filter($entry['availabilities'], static function (array $availability) {
                return $availability['type'] === 'mandant';
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
            $image->setName($name);
            $image->setTranslations($media->getTranslations());

            return $image;
        } catch (Exception $exception) {
            $this->logger->notice('error when parsing product image', [
                'message' => $exception->getMessage(),
                'id' => $entry['id'],
                'url' => $entry['url'],
            ]);
        }

        return null;
    }

    private function getMediaTranslations(array $image, array $productTexts): array
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
