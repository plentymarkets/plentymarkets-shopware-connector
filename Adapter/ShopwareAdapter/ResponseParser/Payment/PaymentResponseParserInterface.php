<?php

namespace ShopwareAdapter\ResponseParser\Payment;

use SystemConnector\TransferObject\TransferObjectInterface;

interface PaymentResponseParserInterface
{
    /**
     * @return TransferObjectInterface[]
     */
    public function parse(array $element): array;
}
