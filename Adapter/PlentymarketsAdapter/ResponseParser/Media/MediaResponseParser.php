<?php

namespace PlentymarketsAdapter\ResponseParser\Media;

use Assert\Assertion;
use InvalidArgumentException;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\Media\Media;
use PlentyConnector\Connector\TransferObject\MediaCategory\MediaCategory;
use PlentymarketsAdapter\Helper\MediaCategoryHelper;
use PlentymarketsAdapter\PlentymarketsAdapter;

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
     * MediaResponseParser constructor.
     *
     * @param IdentityServiceInterface $identityService
     * @param MediaCategoryHelper      $categoryHelper
     */
    public function __construct(
        IdentityServiceInterface $identityService,
        MediaCategoryHelper $categoryHelper
    ) {
        $this->identityService = $identityService;
        $this->categoryHelper  = $categoryHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(array $entry)
    {
        Assertion::url($entry['link']);

        if (empty($entry['filename'])) {
            $entry['filename'] = basename($entry['link']);
        }

        if (!array_key_exists('hash', $entry)) {
            $entry['hash'] = @sha1_file($entry['link']);
        }

        if (empty($entry['hash'])) {
            throw new InvalidArgumentException('');
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

        $entry['hash'] = sha1(json_encode($entry)); // include all fields when computing the hash

        $identity = $this->identityService->findOneOrCreate(
            (string) $entry['hash'],
            PlentymarketsAdapter::NAME,
            Media::TYPE
        );

        $media = new Media();
        $media->setIdentifier($identity->getObjectIdentifier());
        $media->setMediaCategoryIdentifier($entry['mediaCategoryIdentifier']);
        $media->setLink($entry['link']);
        $media->setFilename($entry['filename']);
        $media->setHash($entry['hash']);
        $media->setName($entry['name']);
        $media->setAlternateName($entry['alternateName']);
        $media->setTranslations($entry['translations']);
        $media->setAttributes($entry['attributes']);

        return $media;
    }
}
