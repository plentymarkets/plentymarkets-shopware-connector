<?php

namespace PlentyConnector\Components\Klarna\Plentymarkets;

use PlentyConnector\Components\Klarna\PaymentData\KlarnaPaymentData;
use PlentyConnector\Connector\TransferObject\Payment\Payment;
use PlentymarketsAdapter\RequestGenerator\Payment\PaymentRequestGeneratorInterface;

/**
 * Class KlarnaInstallmentRequestGenerator
 */
class KlarnaRequestGenerator implements PaymentRequestGeneratorInterface
{
    /**
     * @var PaymentRequestGeneratorInterface
     */
    private $parentRequestGenerator;

    /**
     * KlarnaInstallmentRequestGenerator constructor.
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
        $data = $payment->getPaymentData();

        if (!($data instanceof KlarnaPaymentData)) {
            return $paymentParams;
        }

        $paymentParams['property'][] = [
            'typeId' => 2,
            'value' => $data->getTransactionId() . '_' . $data->getPclassId() . '_' . $data->getShopId(),
        ];

        return $paymentParams;
    }
}
