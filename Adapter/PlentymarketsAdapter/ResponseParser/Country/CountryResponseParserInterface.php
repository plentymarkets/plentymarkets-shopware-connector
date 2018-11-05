<?php

namespace PlentymarketsAdapter\ResponseParser\Country;

use SystemConnector\TransferObject\Country\Country;

interface CountryResponseParserInterface
{
    /**
     * @param array $entry
     *
     * @return null|Country
     */
    public function parse(array $entry);
}
