<?php

namespace ShopwareAdapter\ResponseParser\VatRate;

use SystemConnector\TransferObject\VatRate\VatRate;

interface VatRateResponseParserInterface
{
    /**
     * @param array $entry
     *
     * @return null|VatRate
     */
    public function parse(array $entry);
}
