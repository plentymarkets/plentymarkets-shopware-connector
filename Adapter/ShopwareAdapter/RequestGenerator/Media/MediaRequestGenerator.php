<?php

namespace ShopwareAdapter\RequestGenerator\Media;

use Shopware\Models\Media\Album;
use ShopwareAdapter\DataProvider\Media\MediaDataProviderInterface;
use SystemConnector\TransferObject\Media\Media;

class MediaRequestGenerator implements MediaRequestGeneratorInterface
{
    /**
     * @var MediaDataProviderInterface
     */
    private $dataProvider;

    public function __construct(MediaDataProviderInterface $dataProvider)
    {
        $this->dataProvider = $dataProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(Media $media)
    {
        $params = [
            'album' => Album::ALBUM_ARTICLE,
            'file' => $media->getLink(),
            'description' => $media->getAlternateName() ?: $media->getName() ?: $media->getFilename(),
        ];

        if (null !== $media->getMediaCategoryIdentifier()) {
            $params['album'] = $this->dataProvider->getAlbumIdentifierFromMediaObject($media);
        }

        return $params;
    }
}
