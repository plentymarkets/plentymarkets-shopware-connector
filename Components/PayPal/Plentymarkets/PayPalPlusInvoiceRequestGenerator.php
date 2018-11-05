<?php

namespace PlentyConnector\Components\PayPal\Plentymarkets;

use PlentyConnector\Components\PayPal\PaymentData\PayPalPlusInvoicePaymentData;
use PlentymarketsAdapter\RequestGenerator\Payment\PaymentRequestGeneratorInterface;
use SystemConnector\TransferObject\Payment\Payment;

class PayPalPlusInvoiceRequestGenerator implements PaymentRequestGeneratorInterface
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

        if (!($data instanceof PayPalPlusInvoicePaymentData)) {
            return $paymentParams;
        }

        $paymentParams['properties'][] = [
            'typeId' => 22,
            'value' => json_encode([
                'accountHolder' => $data->getAccountHolderName(),
                'bankName' => $data->getBankName(),
                'bic' => $data->getBankIdentifierCode(),
                'iban' => $data->getInternationalBankAccountNumber(),
                'paymentDue' => $data->getPaymentDueDate()->format(DATE_W3C),
                'referenceNumber' => $data->getReferenceNumber(),
            ]),
        ];

        return $paymentParams;
    }
}
