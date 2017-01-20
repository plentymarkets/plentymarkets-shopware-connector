<?php

namespace ShopwareAdapter\ResponseParser\PaymentStatus;

use PlentyConnector\Connector\TransferObject\PaymentStatus\PaymentStatusInterface;

/**
 * Interface PaymentStatusResponseParserInterface
 */
interface PaymentStatusResponseParserInterface
{
    /**
     * @param array $entry
     *
     * @return PaymentStatusInterface|null
     */
    public function parse(array $entry);
}
