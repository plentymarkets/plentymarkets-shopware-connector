<?php

namespace ShopwareAdapter\RequestGenerator\Media;

use PlentyConnector\Connector\TransferObject\Media\Media;

/**
 * Interface MediaRequestGeneratorInterface
 */
interface MediaRequestGeneratorInterface
{
    /**
     * @param Media $media
     *
     * @return array
     */
    public function generate(Media $media);
}
