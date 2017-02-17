<?php

namespace PlentymarketsAdapter\ResponseParser\Country;

use PlentyConnector\Connector\TransferObject\Country\Country;

/**
 * Interface CountryResponseParserInterface
 */
interface CountryResponseParserInterface
{
    /**
     * @param array $entry
     *
     * @return null|Country
     */
    public function parse(array $entry);
}
