<?php

namespace PlentymarketsAdapter\ResponseParser\Media;

use Assert\Assertion;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\Media\Media;
use PlentyConnector\Connector\TransferObject\MediaCategory\MediaCategory;
use PlentymarketsAdapter\Helper\MediaCategoryHelper;
use PlentymarketsAdapter\PlentymarketsAdapter;

/**
 * Class MediaResponseParser.
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
     * OrderStatusResponseParser constructor.
     *
     * @param IdentityServiceInterface $identityService
     * @param MediaCategoryHelper      $categoryHelper
     */
    public function __construct(IdentityServiceInterface $identityService, MediaCategoryHelper $categoryHelper)
    {
        $this->identityService = $identityService;
        $this->categoryHelper = $categoryHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(array $entry)
    {
        Assertion::url($entry['link']);

        if (!array_key_exists('hash', $entry)) {
            $entry['hash'] = sha1_file($entry['link']);
        }

        if (!array_key_exists('name', $entry)) {
            $entry['name'] = '';
        }

        if (!array_key_exists('alternateName', $entry)) {
            $entry['alternateName'] = '';
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

        return Media::fromArray([
            'identifier'              => $identity->getObjectIdentifier(),
            'mediaCategoryIdentifier' => $entry['mediaCategoryIdentifier'],
            'link'                    => $entry['link'],
            'hash'                    => $entry['hash'],
            'name'                    => $entry['name'],
            'alternateName'           => $entry['alternateName'],
            'translations'            => $entry['translations'],
            'attributes'              => $entry['attributes'],
        ]);
    }
}
