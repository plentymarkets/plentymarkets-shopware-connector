<?php

namespace ShopwareAdapter\RequestGenerator\Media;

use SystemConnector\TransferObject\Media\Media;

interface MediaRequestGeneratorInterface
{
    public function generate(Media $media): array;
}
