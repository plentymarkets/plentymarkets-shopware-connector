<?php

namespace ShopwareAdapter\ResponseParser\PaymentStatus;

use PlentyConnector\Connector\TransferObject\PaymentStatus\PaymentStatus;

interface PaymentStatusResponseParserInterface
{
    /**
     * @param array $entry
     *
     * @return null|PaymentStatus
     */
    public function parse(array $entry);
}
