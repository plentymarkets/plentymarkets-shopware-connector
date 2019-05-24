<?php

namespace PlentyConnector\Components\PayPal\Plentymarkets;

use PlentyConnector\Components\PayPal\PaymentData\PayPalUnifiedPaymentData;
use PlentymarketsAdapter\RequestGenerator\Payment\PaymentRequestGeneratorInterface;
use SystemConnector\TransferObject\Payment\Payment;

class PayPalUnifiedRequestGenerator implements PaymentRequestGeneratorInterface
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
    public function generate(Payment $payment) :array
    {
        $paymentParams = $this->parentRequestGenerator->generate($payment);
        $data = $payment->getPaymentData();

        if (!($data instanceof PayPalUnifiedPaymentData)) {
            return $paymentParams;
        }

        $paymentParams['properties'][] = [
            'typeId' => 22,
            'value' => json_encode([
                'accountHolder' => $data->getAccountHolder(),
                'bankName' => $data->getBankName(),
                'bic' => $data->getBic(),
                'iban' => $data->getIban(),
                'paymentDue' => $data->getDueDate()->format(DATE_W3C),
                'referenceNumber' => $data->getReference(),
            ]),
        ];

        return $paymentParams;
    }
}
