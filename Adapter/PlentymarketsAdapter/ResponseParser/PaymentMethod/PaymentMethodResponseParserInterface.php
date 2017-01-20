<?php

namespace PlentymarketsAdapter\ResponseParser\PaymentMethod;

use PlentyConnector\Connector\TransferObject\PaymentMethod\PaymentMethodInterface;

/**
 * Interface PaymentMethodResponseParserInterface
 */
interface PaymentMethodResponseParserInterface
{
    /**
     * @param array $entry
     *
     * @return PaymentMethodInterface|null
     */
    public function parse(array $entry);
}
