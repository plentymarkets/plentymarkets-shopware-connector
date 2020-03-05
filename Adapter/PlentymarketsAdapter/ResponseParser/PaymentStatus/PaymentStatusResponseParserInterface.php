<?php

namespace PlentymarketsAdapter\ResponseParser\PaymentStatus;

use SystemConnector\TransferObject\PaymentStatus\PaymentStatus;

interface PaymentStatusResponseParserInterface
{
    /**
     * @return null|PaymentStatus
     */
    public function parse(array $entry);
}
