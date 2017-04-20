<?php

namespace PlentymarketsAdapter\RequestGenerator\Payment;

use PlentyConnector\Connector\TransferObject\Payment\Payment;

/**
 * Interface PaymentRequestGeneratorInterface
 */
interface PaymentRequestGeneratorInterface
{
    /**
     * @param Payment $payment
     *
     * @return array
     */
    public function generate(Payment $payment);
}
