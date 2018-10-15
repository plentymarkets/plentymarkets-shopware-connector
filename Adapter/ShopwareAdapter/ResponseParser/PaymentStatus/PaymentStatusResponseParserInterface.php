<?php

namespace ShopwareAdapter\ResponseParser\PaymentStatus;

use SystemConnector\TransferObject\PaymentStatus\PaymentStatus;

interface PaymentStatusResponseParserInterface
{
    /**
     * @param array $entry
     *
     * @return null|PaymentStatus
     */
    public function parse(array $entry);
}
