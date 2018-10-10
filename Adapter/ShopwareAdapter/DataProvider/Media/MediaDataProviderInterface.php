<?php

namespace ShopwareAdapter\DataProvider\Media;

use SystemConnector\TransferObject\Media\Media;

interface MediaDataProviderInterface
{
    /**
     * @param Media $media
     *
     * @return string
     */
    public function getAlbumIdentifierFromMediaObject(Media $media);

    /**
     * @param Media $media
     *
     * @return string
     */
    public function getMediaHashForMediaObject(Media $media);
}
