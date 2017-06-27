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

            $content = @file_get_contents($entry['link']);

            if (false === $content) {
                $this->logger->warning('could not load media file - ' . $entry['link']);

                return null;
            }

            $content = base64_encode($content);

            if (empty($entry['filename'])) {
                $entry['filename'] = basename($entry['link']);
            }

            if (!array_key_exists('hash', $entry)) {
                $entry['hash'] = sha1($content);
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

            $media = new Media();
            $media->setIdentifier($identity->getObjectIdentifier());
            $media->setMediaCategoryIdentifier($entry['mediaCategoryIdentifier']);
            $media->setContent($content);
            $media->setFilename($entry['filename']);
            $media->setHash($entry['hash']);
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
