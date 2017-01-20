<?php

namespace PlentymarketsAdapter\ResponseParser\VatRate;

use PlentyConnector\Connector\TransferObject\VatRate\VatRateInterface;

/**
 * Interface VatRateResponseParserInterface
 */
interface VatRateResponseParserInterface
{
    /**
     * @param array $entry
     *
     * @return VatRateInterface|null
     */
    public function parse(array $entry);
}
