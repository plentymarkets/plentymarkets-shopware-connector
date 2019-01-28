<?php

namespace PlentyConnector\Components\AmazonPay\Plentymarkets;

use PlentyConnector\Components\AmazonPay\PaymentData\AmazonPayPaymentData;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\RequestGenerator\Payment\PaymentRequestGeneratorInterface;
use SystemConnector\IdentityService\IdentityServiceInterface;
use SystemConnector\TransferObject\Order\Order;
use SystemConnector\TransferObject\Payment\Payment;

class AmazonPayRequestGenerator implements PaymentRequestGeneratorInterface
{
    /**
     * @var PaymentRequestGeneratorInterface
     */
    private $parentRequestGenerator;

    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    public function __construct(
        PaymentRequestGeneratorInterface $parentRequestGenerator,
        IdentityServiceInterface $identityService
    ){
        $this->parentRequestGenerator = $parentRequestGenerator;
        $this->identityService = $identityService;

    }

    /**
     * {@inheritdoc}
     */
    public function generate(Payment $payment)
    {
        $paymentParams = $this->parentRequestGenerator->generate($payment);
        $data = $payment->getPaymentData();

        if (!($data instanceof AmazonPayPaymentData)) {
            return $paymentParams;
        }

        $orderIdentity = $this->identityService->findOneBy([
            'objectIdentifier' => $payment->getOrderIdentifer(),
            'objectType' => Order::TYPE,
            'adapterName' => PlentymarketsAdapter::NAME,
        ]);



        return $paymentParams;
    }
}
