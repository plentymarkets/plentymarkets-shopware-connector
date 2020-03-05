<?php

namespace PlentymarketsAdapter\ResponseParser\Country;

use SystemConnector\TransferObject\Country\Country;

interface CountryResponseParserInterface
{
    /**
     * @return null|Country
     */
    public function parse(array $entry);
}
