<?php

namespace ShopwareAdapter\ResponseParser\PaymentMethod;

use PlentyConnector\Connector\TransferObject\PaymentMethod\PaymentMethod;

interface PaymentMethodResponseParserInterface
{
    /**
     * @param array $entry
     *
     * @return null|PaymentMethod
     */
    public function parse(array $entry);
}
