<?php

namespace ShopwareAdapter\RequestGenerator\Media;

use PlentyConnector\Connector\TransferObject\Media\Media;

interface MediaRequestGeneratorInterface
{
    /**
     * @param Media $media
     *
     * @return array
     */
    public function generate(Media $media);
}
