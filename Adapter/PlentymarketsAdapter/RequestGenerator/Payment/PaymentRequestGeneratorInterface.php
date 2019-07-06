<?php

namespace PlentymarketsAdapter\RequestGenerator\Payment;

use SystemConnector\TransferObject\Payment\Payment;

interface PaymentRequestGeneratorInterface
{
    /**
     * @param Payment $payment
     *
     * @return array
     */
    public function generate(Payment $payment): array;
}
