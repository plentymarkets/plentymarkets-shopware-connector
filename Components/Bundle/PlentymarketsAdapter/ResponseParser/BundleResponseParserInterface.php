<?php

namespace PlentyConnector\Components\Bundle\PlentymarketsAdapter\ResponseParser;

interface BundleResponseParserInterface
{
    /**
     * @param array $product
     */
    public function parse(array $product);
}
