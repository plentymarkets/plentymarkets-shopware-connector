<?php

namespace ShopwareAdapter\ResponseParser\Payment;

use Assert\Assertion;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\Currency\Currency;
use PlentyConnector\Connector\TransferObject\Order\Order;
use PlentyConnector\Connector\TransferObject\Payment\Payment;
use PlentyConnector\Connector\TransferObject\PaymentMethod\PaymentMethod;
use Shopware\Components\Model\ModelRepository;
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
     * {@inheritdoc}
     */
    public function parse(array $element)
    {
        $paymentIdentifier = $this->identityService->findOneOrCreate(
            (string) $element['id'],
            ShopwareAdapter::NAME,
            Payment::TYPE
        )->getObjectIdentifier();

        if (empty($element['paymentStatus'])) {
            return [];
        }

        if (Status::PAYMENT_STATE_COMPLETELY_PAID !== $element['paymentStatus']['id']) {
            return [];
        }

        $payment = new Payment();
        $payment->setIdentifier($paymentIdentifier);
        $payment->setOrderIdentifer($this->getIdentifier($element['id'], Order::TYPE));
        $payment->setAmount($element['invoiceAmount']);
        $payment->setCurrencyIdentifier($this->getIdentifier($this->getCurrencyId($element['currency']), Currency::TYPE));
        $payment->setPaymentMethodIdentifier($this->getIdentifier($element['paymentId'], PaymentMethod::TYPE));
        $payment->setTransactionReference($element['transactionId']);

        return [$payment];
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
         * @var ModelRepository $currencyRepository
         */
        $currencyRepository = Shopware()->Models()->getRepository(CurrencyModel::class);

        return $currencyRepository->findOneBy(['currency' => $currency])->getId();
    }
}
