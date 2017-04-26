<?php

namespace PlentyConnector\Components\PayPal\Plentymarkets;

use PlentyConnector\Components\PayPal\PaymentData\PayPalPlusInvoicePaymentData;
use PlentyConnector\Connector\TransferObject\Payment\Payment;
use PlentymarketsAdapter\RequestGenerator\Payment\PaymentRequestGeneratorInterface;

/**
 * Class PayPalPlusInvoiceRequestGenerator
 */
class PayPalPlusInvoiceRequestGenerator implements PaymentRequestGeneratorInterface
{
    /**
     * @var PaymentRequestGeneratorInterface
     */
    private $parentRequestGenerator;

    /**
     * PayPalPlusInvoiceRequestGenerator constructor.
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
