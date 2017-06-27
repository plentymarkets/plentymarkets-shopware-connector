<?php

namespace ShopwareAdapter\ResponseParser\Payment;

use Assert\Assertion;
use InvalidArgumentException;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\Currency\Currency;
use PlentyConnector\Connector\TransferObject\Order\Order;
use PlentyConnector\Connector\TransferObject\Payment\Payment;
use PlentyConnector\Connector\TransferObject\PaymentMethod\PaymentMethod;
use PlentyConnector\Connector\TransferObject\Shop\Shop;
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

        $shopIdentity = $this->identityService->findOneOrThrow(
            (string) $element['shopId'],
            ShopwareAdapter::NAME,
            Shop::TYPE
        );

        $isMappedIdentity = $this->identityService->isMapppedIdentity(
            $shopIdentity->getObjectIdentifier(),
            $shopIdentity->getObjectType(),
            $shopIdentity->getAdapterName()
        );

        if (!$isMappedIdentity) {
            return [];
        }

        $currencyIdentifier = $this->getIdentifier($this->getCurrencyId($element['currency']), Currency::TYPE);

        $payment = new Payment();
        $payment->setIdentifier($paymentIdentifier);
        $payment->setShopIdentifier($shopIdentity->getObjectIdentifier());
        $payment->setOrderIdentifer($this->getIdentifier($element['id'], Order::TYPE));
        $payment->setAmount($element['invoiceAmount']);
        $payment->setAccountHolder($this->getAccountHolder($element));
        $payment->setCurrencyIdentifier($currencyIdentifier);
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

        $currencyModel = $currencyRepository->findOneBy(['currency' => $currency]);

        if (null === $currencyModel) {
            throw new InvalidArgumentException('invalid currency code');
        }

        return $currencyModel->getId();
    }

    /**
     * @param array $element
     *
     * @return string
     */
    private function getAccountHolder(array $element)
    {
        $firstName = !empty($element['billing']['firstName']) ? $element['billing']['firstName'] : '';
        $lastName = !empty($element['billing']['lastName']) ? $element['billing']['lastName'] : '';

        return trim(sprintf('%s %s', $firstName, $lastName));
    }
}
