<?php

namespace ShopwareAdapter\DataProvider\Media;

use Exception;
use Shopware\Bundle\AttributeBundle\Service\DataLoader;
use ShopwareAdapter\ShopwareAdapter;
use SystemConnector\IdentityService\Exception\NotFoundException;
use SystemConnector\IdentityService\IdentityServiceInterface;
use SystemConnector\TransferObject\Media\Media;
use SystemConnector\TransferObject\MediaCategory\MediaCategory;

class MediaDataProvider implements MediaDataProviderInterface
{
    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * @var DataLoader
     */
    private $dataLoader;

    public function __construct(IdentityServiceInterface $identityService, DataLoader $dataLoader)
    {
        $this->identityService = $identityService;
        $this->dataLoader = $dataLoader;
    }

    /**
     * @param Media $media
     *
     * @throws NotFoundException
     *
     * @return string
     */
    public function getAlbumIdentifierFromMediaObject(Media $media): string
    {
        $mediaCategoryIdentity = $this->identityService->findOneBy([
            'objectIdentifier' => $media->getMediaCategoryIdentifier(),
            'objectType' => MediaCategory::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
        ]);

        if (null === $mediaCategoryIdentity) {
            throw new NotFoundException('missing media category for adapter');
        }

        return $mediaCategoryIdentity->getAdapterIdentifier();
    }

    /**
     * @param Media $media
     *
     * @throws Exception
     *
     * @return string
     */
    public function getMediaHashForMediaObject(Media $media): string
    {
        $identity = $this->identityService->findOneBy([
            'objectIdentifier' => $media->getIdentifier(),
            'objectType' => Media::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
        ]);

        if (null === $identity) {
            return '';
        }

        $attributes = $this->dataLoader->load('s_media_attributes', $identity->getAdapterIdentifier());

        if (empty($attributes)) {
            return '';
        }

        foreach ($attributes as $key => $value) {
            if ($key !== 'plenty_connector_hash') {
                continue;
            }

            return $value;
        }

        return '';
    }
}
