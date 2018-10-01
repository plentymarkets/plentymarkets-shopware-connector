<?php

namespace PlentymarketsAdapter\ResponseParser\Product\Image;

interface ImageResponseParserInterface
{
    /**
     * @param array $entry
     * @param array $texts
     * @param array $result
     *
     * @return mixed
     */
    public function parseImage(array $entry, array $texts, array &$result);
}
