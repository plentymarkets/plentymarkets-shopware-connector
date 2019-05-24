<?php

namespace ShopwareAdapter\ResponseParser\Payment;

use SystemConnector\TransferObject\TransferObjectInterface;

interface PaymentResponseParserInterface
{
    /**
     * @param array $element
     *
     * @return TransferObjectInterface[]
     */
    public function parse(array $element): array;
}
