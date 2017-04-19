<?php

namespace ShopwareAdapter\ResponseParser\Payment;

use Assert\Assertion;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\Currency\Currency;
use PlentyConnector\Connector\TransferObject\Order\Order;
use PlentyConnector\Connector\TransferObject\Payment\Payment;
use PlentyConnector\Connector\TransferObject\PaymentMethod\PaymentMethod;
use Shopware\Models\Order\Status;
use Shopware\Models\Shop\Currency as CurrencyModel;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class PaymentResponseParser
 */
class PaymentResponseParser implements PaymentResponseParserInterface
{
    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * OrderStatusResponseParser constructor.
     *
     * @param IdentityServiceInterface $identityService
     */
    public function __construct(IdentityServiceInterface $identityService)
    {
        $this->identityService = $identityService;
    }

    /**
     * @param int    $entry
     * @param string $type
     *
     * @return string
     */
    private function getIdentifier($entry, $type)
    {
        Assertion::integerish($entry);

        return $this->identityService->findOneOrThrow(
            (string) $entry,
            ShopwareAdapter::NAME,
            $type
        )->getObjectIdentifier();
    }

    /**
     * @param string $currency
     *
     * @return int
     */
    private function getCurrencyId($currency)
    {
        /**
         * @var ModelRepository $currencyRepo
         */
        $currencyRepo = Shopware()->Models()->getRepository(CurrencyModel::class);

        return $currencyRepo->findOneBy(['currency' => $currency])->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function parse(array $element)
    {
        $payments = [];

        $paymentIdentifier = $this->identityService->findOneOrCreate(
            (string) $element['id'],
            ShopwareAdapter::NAME,
            Payment::TYPE
        )->getObjectIdentifier();

        $orderIdentifier = $this->getIdentifier($element['id'], Order::TYPE);

        $currencyIdentifier = $this->getIdentifier($this->getCurrencyId($element['currency']), Currency::TYPE);
        $paymentMethodIdentifier = $this->getIdentifier($element['paymentId'], PaymentMethod::TYPE);

        if (!empty($element['paymentStatus'])) {
            if (Status::PAYMENT_STATE_COMPLETELY_PAID === $element['paymentStatus']['id']) {
                $payment = new Payment();
                $payment->setIdentifier($paymentIdentifier);
                $payment->setOrderIdentifer($orderIdentifier);
                $payment->setAmount($element['invoiceAmount']);
                $payment->setCurrencyIdentifier($currencyIdentifier);
                $payment->setPaymentMethodIdentifier($paymentMethodIdentifier);
                $payment->setTransactionReference($element['transactionId']);

                $payments[] = $payment;
            }
        }

        return $payments;
    }
}
