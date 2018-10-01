<?php

namespace PlentymarketsAdapter\ResponseParser\VatRate;

use PlentyConnector\Connector\TransferObject\VatRate\VatRate;

interface VatRateResponseParserInterface
{
    /**
     * @param array $entry
     *
     * @return null|VatRate
     */
    public function parse(array $entry);
}
