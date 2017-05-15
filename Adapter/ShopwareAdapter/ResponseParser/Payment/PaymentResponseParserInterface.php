<?php

namespace ShopwareAdapter\ResponseParser\Payment;

use PlentyConnector\Connector\TransferObject\TransferObjectInterface;

/**
 * Interface PaymentResponseParserInterface
 */
interface PaymentResponseParserInterface
{
    /**
     * @param array $element
     *
     * @return TransferObjectInterface[]
     */
    public function parse(array $element);
}
