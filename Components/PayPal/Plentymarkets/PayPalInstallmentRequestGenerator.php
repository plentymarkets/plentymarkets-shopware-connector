<?php

namespace PlentyConnector\Components\PayPal\Plentymarkets;

use PlentyConnector\Components\PayPal\PaymentData\PayPalInstallmentPaymentData;
use PlentymarketsAdapter\RequestGenerator\Payment\PaymentRequestGeneratorInterface;
use SystemConnector\TransferObject\Payment\Payment;

class PayPalInstallmentRequestGenerator implements PaymentRequestGeneratorInterface
{
    /**
     * @var PaymentRequestGeneratorInterface
     */
    private $parentRequestGenerator;

    public function __construct(PaymentRequestGeneratorInterface $parentRequestGenerator)
    {
        $this->parentRequestGenerator = $parentRequestGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(Payment $payment)
    {
        $paymentParams = $this->parentRequestGenerator->generate($payment);
        $data = $payment->getPaymentData();

        if (!($data instanceof PayPalInstallmentPaymentData)) {
            return $paymentParams;
        }

        $paymentParams['properties'][] = [
            'typeId' => 22,
            'value' => json_encode([
                'currency' => $data->getCurrency(),
                'financingCosts' => $data->getFinancingCosts(),
                'totalCostsIncludeFinancing' => $data->getTotalCostsIncludeFinancing(),
            ]),
        ];

        return $paymentParams;
    }
}
