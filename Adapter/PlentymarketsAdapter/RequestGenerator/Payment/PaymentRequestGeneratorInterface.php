<?php

namespace PlentymarketsAdapter\RequestGenerator\Payment;

use SystemConnector\TransferObject\Payment\Payment;

interface PaymentRequestGeneratorInterface
{
    public function generate(Payment $payment): array;
}
