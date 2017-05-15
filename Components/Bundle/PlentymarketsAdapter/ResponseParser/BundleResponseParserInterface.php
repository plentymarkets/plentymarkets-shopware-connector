<?php

namespace PlentyConnector\Components\Bundle\PlentymarketsAdapter\ResponseParser;

/**
 * Interface BundleResponseParserInterface
 */
interface BundleResponseParserInterface
{
    /**
     * @param array $product
     */
    public function parse(array $product);
}
