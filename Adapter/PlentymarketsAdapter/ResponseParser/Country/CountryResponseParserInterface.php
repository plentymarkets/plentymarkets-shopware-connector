<?php

namespace PlentymarketsAdapter\ResponseParser\Country;

use PlentyConnector\Connector\TransferObject\Country\CountryInterface;

/**
 * Interface CountryResponseParserInterface
 */
interface CountryResponseParserInterface
{
    /**
     * @param array $entry
     *
     * @return CountryInterface|null
     */
    public function parse(array $entry);
}
