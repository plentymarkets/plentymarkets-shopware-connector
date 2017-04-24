<?php

namespace PlentymarketsAdapter\RequestGenerator\Payment;

use PlentyConnector\Connector\IdentityService\Exception\NotFoundException;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\Currency\Currency;
use PlentyConnector\Connector\TransferObject\Payment\Payment;
use PlentyConnector\Connector\TransferObject\PaymentMethod\PaymentMethod;
use PlentymarketsAdapter\PlentymarketsAdapter;

/**
 * Class PaymentRequestGenerator
 */
class PaymentRequestGenerator implements PaymentRequestGeneratorInterface
{
    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * PaymentRequestGenerator constructor.
     *
     * @param IdentityServiceInterface $identityService
     */
    public function __construct(IdentityServiceInterface $identityService)
    {
        $this->identityService = $identityService;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(Payment $payment)
    {
        $paymentMethodIdentity = $this->identityService->findOneBy([
            'objectIdentifier' => $payment->getPaymentMethodIdentifier(),
            'objectType' => PaymentMethod::TYPE,
            'adapterName' => PlentymarketsAdapter::NAME,
        ]);

        if (null === $paymentMethodIdentity) {
            throw new NotFoundException('payment method not mapped');
        }

        $currencyIdentity = $this->identityService->findOneBy([
            'objectIdentifier' => $payment->getCurrencyIdentifier(),
            'objectType' => Currency::TYPE,
            'adapterName' => PlentymarketsAdapter::NAME,
        ]);

        if (null === $currencyIdentity) {
            throw new NotFoundException('currency not mapped');
        }

        $paymentParams = [
            'amount' => $payment->getAmount(),
            'exchangeRatio' => 1,
            'mopId' => $paymentMethodIdentity->getAdapterIdentifier(),
            'currency' => $currencyIdentity->getAdapterIdentifier(),
            'type' => 'credit',
            'transactionType' => 2,
            'status' => 2,
        ];

        /**
         * Payment origin = 23
         * Name of the sender = 11
         * Email of the sender = 12
         * Transaction ID = 1
         * Booking text = 3
         * Shipping address ID = 24
         * Invoice address ID = 25
         */
        $paymentParams['property'] = [
            [
                'typeId' => 23,
                'value' => 'shopware',
            ],
            [
                'typeId' => 1,
                'value' => $payment->getTransactionReference(),
            ],
            [
                'typeId' => 3,
                'value' => 'booked',
            ],
        ];

        return $paymentParams;
    }
}
