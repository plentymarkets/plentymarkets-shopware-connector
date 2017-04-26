<?php

namespace PlentyConnector\Components\PayPal\Plentymarkets;

use PlentyConnector\Components\PayPal\PaymentData\PayPalInstallmentPaymentData;
use PlentyConnector\Connector\TransferObject\Payment\Payment;
use PlentymarketsAdapter\RequestGenerator\Payment\PaymentRequestGeneratorInterface;

/**
 * Class PayPalInstallmentRequestGenerator
 */
class PayPalInstallmentRequestGenerator implements PaymentRequestGeneratorInterface
{
    /**
     * @var PaymentRequestGeneratorInterface
     */
    private $parentRequestGenerator;

    /**
     * PayPalInstallmentRequestGenerator constructor.
     *
     * @param PaymentRequestGeneratorInterface $parentRequestGenerator
     */
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

        if (!($payment->getPaymentData() instanceof PayPalInstallmentPaymentData)) {
            return $paymentParams;
        }

        /**
         * @var PayPalInstallmentPaymentData $data
         */
        $data = $payment->getPaymentData();

        $paymentParams['property'][] = [
            'typeId' => 22,
            'value' => [
                'currency' => $data->getCurrency(),
                'financingCosts' => $data->getFinancingCosts(),
                'totalCostsIncludeFinancing' => $data->getTotalCostsIncludeFinancing(),
            ],
        ];

        return $paymentParams;
    }
}
