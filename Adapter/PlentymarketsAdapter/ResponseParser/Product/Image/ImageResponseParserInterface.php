<?php

namespace PlentymarketsAdapter\ResponseParser\Product\Image;

interface ImageResponseParserInterface
{
    /**
     * @return mixed
     */
    public function parseImage(array $entry, array $texts, array &$result);
}
