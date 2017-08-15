<?php

namespace ShopwareAdapter\DataProvider\Media;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\Media\Media;
use PlentyConnector\Connector\TransferObject\MediaCategory\MediaCategory;
use Shopware\Bundle\AttributeBundle\Service\DataLoader;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class MediaDataProvider
 */
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

    /**
     * MediaDataProvider constructor.
     * @param IdentityServiceInterface $identityService
     * @param DataLoader $dataLoader
     */
    public function __construct(IdentityServiceInterface $identityService, DataLoader $dataLoader)
    {
        $this->identityService = $identityService;
        $this->dataLoader = $dataLoader;
    }

    /**
     * @param Media $media
     *
     * @return string
     */
    public function getAlbumIdentifierFromMediaObject(Media $media)
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
     * @return string
     */
    public function getMediaHashForMediaObject(Media $media)
    {
        $identity = $this->identityService->findOneBy([
            'objectIdentifier' => (string) $media->getIdentifier(),
            'objectType' => Media::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
        ]);

        if (null === $identity) {
            return '';
        }

        $attributes = $this->dataLoader->load('s_media_attributes', $identity->getAdapterIdentifier());

        foreach ($attributes as $key => $value) {
            if ($key !== 'plenty_connector_hash') {
                continue;
            }

            return $value;
        }

        return '';
    }
}
