<?php

namespace PlentymarketsAdapter\ResponseParser\Media;

use Assert\Assertion;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\Media\Media;
use PlentyConnector\Connector\TransferObject\MediaCategory\MediaCategory;
use PlentymarketsAdapter\Helper\MediaCategoryHelper;
use PlentymarketsAdapter\PlentymarketsAdapter;
use Psr\Log\LoggerInterface;

/**
 * Class MediaResponseParser
 */
class MediaResponseParser implements MediaResponseParserInterface
{
    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * @var MediaCategoryHelper
     */
    private $categoryHelper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * MediaResponseParser constructor.
     *
     * @param IdentityServiceInterface $identityService
     * @param MediaCategoryHelper      $categoryHelper
     * @param LoggerInterface          $logger
     */
    public function __construct(
        IdentityServiceInterface $identityService,
        MediaCategoryHelper $categoryHelper,
        LoggerInterface $logger
    ) {
        $this->identityService = $identityService;
        $this->categoryHelper = $categoryHelper;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(array $entry)
    {
        try {
            Assertion::url($entry['link']);

            if (empty($entry['filename'])) {
                $entry['filename'] = basename($entry['link']);
            }

            if (!array_key_exists('hash', $entry)) {
                $entry['hash'] = sha1_file($entry['link']);
            }

            if (empty($entry['name'])) {
                $entry['name'] = null;
            }

            if (empty($entry['alternateName'])) {
                $entry['alternateName'] = null;
            }

            if (!array_key_exists('translations', $entry)) {
                $entry['translations'] = [];
            }

            if (!array_key_exists('attributes', $entry)) {
                $entry['attributes'] = [];
            }

            if (array_key_exists('mediaCategory', $entry)) {
                $mediaCategories = $this->categoryHelper->getCategories();

                $mediaCategoryIdentity = $this->identityService->findOneOrCreate(
                    (string) $mediaCategories[$entry['mediaCategory']]['id'],
                    PlentymarketsAdapter::NAME,
                    MediaCategory::TYPE
                );

                $entry['mediaCategoryIdentifier'] = $mediaCategoryIdentity->getObjectIdentifier();
            } else {
                $entry['mediaCategoryIdentifier'] = null;
            }

            $identity = $this->identityService->findOneOrCreate(
                (string) $entry['hash'],
                PlentymarketsAdapter::NAME,
                Media::TYPE
            );

            $entry['identifier'] = $identity->getObjectIdentifier();

            $media = new Media();
            $media->setIdentifier($entry['identity']);
            $media->setMediaCategoryIdentifier($entry['mediaCategoryIdentifier']);
            $media->setLink($entry['link']);
            $media->setFilename($entry['filename']);
            $media->setHash(sha1(json_encode($entry))); // include fields when computing the hash
            $media->setName($entry['name']);
            $media->setAlternateName($entry['alternateName']);
            $media->setTranslations($entry['translations']);
            $media->setAttributes($entry['attributes']);

            return $media;
        } catch (\Exception $exception) {
            $this->logger->warning($exception->getMessage());

            return null;
        }
    }
}
