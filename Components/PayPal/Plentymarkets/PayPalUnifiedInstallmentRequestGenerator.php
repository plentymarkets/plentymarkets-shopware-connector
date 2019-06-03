<?php

namespace PlentyConnector\Components\PayPal\Plentymarkets;

use PlentyConnector\Components\PayPal\PaymentData\PayPalUnifiedInstallmentPaymentData;
use PlentymarketsAdapter\RequestGenerator\Payment\PaymentRequestGeneratorInterface;
use SystemConnector\TransferObject\Payment\Payment;

class PayPalUnifiedInstallmentRequestGenerator implements PaymentRequestGeneratorInterface
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
    public function generate(Payment $payment): array
    {
        $paymentParams = $this->parentRequestGenerator->generate($payment);
        $data = $payment->getPaymentData();

        if (!($data instanceof PayPalUnifiedInstallmentPaymentData)) {
            return $paymentParams;
        }

        $paymentParams['properties'][] = [
            'typeId' => 22,
            'value' => json_encode([
                'financingCosts' => $data->getFeeAmount(),
                'totalCostsIncludeFinancing' => $data->getTotalCost(),
                'monthlyPayment' => $data->getMonthlyPayment(),
                'term' => $data->getTerm(),
            ]),
        ];

        return $paymentParams;
    }
}
