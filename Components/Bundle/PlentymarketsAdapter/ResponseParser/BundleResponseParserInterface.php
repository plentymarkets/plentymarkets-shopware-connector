<?php

namespace PlentyConnector\Components\Bundle\PlentymarketsAdapter\ResponseParser;

interface BundleResponseParserInterface
{
    public function parse(array $product);
}
