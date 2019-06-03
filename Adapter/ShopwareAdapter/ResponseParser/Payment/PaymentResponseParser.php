<?php

namespace ShopwareAdapter\ResponseParser\Payment;

use Assert\Assertion;
use Assert\AssertionFailedException;
use Psr\Log\LoggerInterface;
use Shopware\Models\Order\Status;
use ShopwareAdapter\DataProvider\Currency\CurrencyDataProviderInterface;
use ShopwareAdapter\ShopwareAdapter;
use SystemConnector\IdentityService\IdentityServiceInterface;
use SystemConnector\TransferObject\Currency\Currency;
use SystemConnector\TransferObject\Order\Order;
use SystemConnector\TransferObject\Payment\Payment;
use SystemConnector\TransferObject\PaymentMethod\PaymentMethod;
use SystemConnector\TransferObject\Shop\Shop;

class PaymentResponseParser implements PaymentResponseParserInterface
{
    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * @var CurrencyDataProviderInterface
     */
    private $currencyDataProvider;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        IdentityServiceInterface $identityService,
        CurrencyDataProviderInterface $currencyDataProvider,
        LoggerInterface $logger
    ) {
        $this->identityService = $identityService;
        $this->currencyDataProvider = $currencyDataProvider;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(array $element): array
    {
        $paymentIdentifier = $this->identityService->findOneOrCreate(
            (string) $element['id'],
            ShopwareAdapter::NAME,
            Payment::TYPE
        );

        $isMappedPaymentIdentity = $this->identityService->isMappedIdentity(
            $paymentIdentifier->getObjectIdentifier(),
            $paymentIdentifier->getObjectType(),
            $paymentIdentifier->getAdapterName()
        );

        if ($isMappedPaymentIdentity) {
            $this->logger->notice('paymentidentity' . $paymentIdentifier->getObjectIdentifier() . ' ist not mapped');

            return [];
        }

        if (empty($element['paymentStatus'])) {
            return [];
        }

        if (Status::PAYMENT_STATE_COMPLETELY_PAID !== $element['paymentStatus']['id']) {
            return [];
        }

        if (empty($element['transactionId'])) {
            $element['transactionId'] = $paymentIdentifier->getObjectIdentifier();
        }

        $shopIdentity = $this->identityService->findOneOrThrow(
            (string) $element['shopId'],
            ShopwareAdapter::NAME,
            Shop::TYPE
        );

        $isMappedShopIdentity = $this->identityService->isMappedIdentity(
            $shopIdentity->getObjectIdentifier(),
            $shopIdentity->getObjectType(),
            $shopIdentity->getAdapterName()
        );

        if (!$isMappedShopIdentity) {
            $this->logger->warning('shopidentity' . $shopIdentity->getObjectIdentifier() . ' ist not mapped');

            return [];
        }

        $shopwareCurrencyIdentifier = $this->currencyDataProvider->getCurrencyIdentifierByCode($element['currency']);
        $currencyIdentifier = $this->getConnectorIdentifier($shopwareCurrencyIdentifier, Currency::TYPE);

        $payment = new Payment();
        $payment->setIdentifier($paymentIdentifier->getObjectIdentifier());
        $payment->setShopIdentifier($shopIdentity->getObjectIdentifier());
        $payment->setOrderIdentifier($this->getConnectorIdentifier($element['id'], Order::TYPE));
        $payment->setAmount($element['invoiceAmount']);
        $payment->setAccountHolder($this->getAccountHolder($element));
        $payment->setCurrencyIdentifier($currencyIdentifier);
        $payment->setPaymentMethodIdentifier($this->getConnectorIdentifier($element['paymentId'], PaymentMethod::TYPE));
        $payment->setTransactionReference($element['transactionId']);

        return [$payment];
    }

    /**
     * @param int    $entry
     * @param string $type
     *
     * @throws AssertionFailedException
     *
     * @return string
     */
    private function getConnectorIdentifier($entry, $type): string
    {
        Assertion::integerish($entry);

        return $this->identityService->findOneOrThrow(
            (string) $entry,
            ShopwareAdapter::NAME,
            $type
        )->getObjectIdentifier();
    }

    /**
     * @param array $element
     *
     * @return string
     */
    private function getAccountHolder(array $element): string
    {
        $firstName = !empty($element['billing']['firstName']) ? $element['billing']['firstName'] : '';
        $lastName = !empty($element['billing']['lastName']) ? $element['billing']['lastName'] : '';

        return trim(sprintf('%s %s', $firstName, $lastName));
    }
}
