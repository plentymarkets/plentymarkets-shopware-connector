<?php

namespace ShopwareAdapter\ResponseParser\Country;

use PlentyConnector\Connector\TransferObject\Country\Country;

interface CountryResponseParserInterface
{
    /**
     * @param array $entry
     *
     * @return null|Country
     */
    public function parse(array $entry);
}
