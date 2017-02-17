<?php

namespace PlentymarketsAdapter\ResponseParser\PaymentStatus;

use PlentyConnector\Connector\TransferObject\PaymentStatus\PaymentStatus;

/**
 * Interface PaymentStatusResponseParserInterface
 */
interface PaymentStatusResponseParserInterface
{
    /**
     * @param array $entry
     *
     * @return null|PaymentStatus
     */
    public function parse(array $entry);
}
