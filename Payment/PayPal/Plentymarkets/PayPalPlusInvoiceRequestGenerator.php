<?php

namespace PlentyConnector\Payment\PayPal\Plentymarkets;

use PlentyConnector\Connector\TransferObject\Payment\Payment;
use PlentyConnector\Payment\PayPal\PaymentData\PayPalPlusInvoicePaymentData;
use PlentymarketsAdapter\RequestGenerator\Payment\PaymentRequestGeneratorInterface;

/**
 * Class PayPalPaymentRequestGenerator
 */
class PayPalPlusInvoiceRequestGenerator implements PaymentRequestGeneratorInterface
{
    /**
     * @var PaymentRequestGeneratorInterface
     */
    private $parentRequestGenerator;

    /**
     * PayPalPaymentRequestGenerator constructor.
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

        if (!($payment->getPaymentData() instanceof PayPalPlusInvoicePaymentData)) {
            return $paymentParams;
        }

        /**
         * @var PayPalPlusInvoicePaymentData $data
         */
        $data = $payment->getPaymentData();

        $paymentParams['property'][] = [
            'typeId' => 22,
            'value' => [
                'accountHolder' => $data->getAccountHolderName(),
                'bankName' => $data->getBankName(),
                'bic' => $data->getBankIdentifierCode(),
                'iban' => $data->getInternationalBankAccountNumber(),
                'paymentDueDate' => $data->getPaymentDueDate()->format(DATE_W3C),
                'reference' => $data->getReferenceNumber(),
            ],
        ];

        return $paymentParams;
    }
}
