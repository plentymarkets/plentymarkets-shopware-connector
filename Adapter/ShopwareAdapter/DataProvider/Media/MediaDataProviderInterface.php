<?php

namespace ShopwareAdapter\DataProvider\Media;

use SystemConnector\TransferObject\Media\Media;

interface MediaDataProviderInterface
{
    public function getAlbumIdentifierFromMediaObject(Media $media): string;

    public function getMediaHashForMediaObject(Media $media): string;
}
