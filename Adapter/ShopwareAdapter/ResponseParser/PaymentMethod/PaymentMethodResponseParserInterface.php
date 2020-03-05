<?php

namespace ShopwareAdapter\ResponseParser\PaymentMethod;

use SystemConnector\TransferObject\PaymentMethod\PaymentMethod;

interface PaymentMethodResponseParserInterface
{
    /**
     * @return null|PaymentMethod
     */
    public function parse(array $entry);
}
