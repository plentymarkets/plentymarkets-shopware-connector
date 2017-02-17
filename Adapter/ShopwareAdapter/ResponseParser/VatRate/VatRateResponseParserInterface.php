<?php

namespace ShopwareAdapter\ResponseParser\VatRate;

use PlentyConnector\Connector\TransferObject\VatRate\VatRate;

/**
 * Interface VatRateResponseParserInterface
 */
interface VatRateResponseParserInterface
{
    /**
     * @param array $entry
     *
     * @return null|VatRate
     */
    public function parse(array $entry);
}
