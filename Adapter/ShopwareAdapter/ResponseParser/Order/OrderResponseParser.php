<?php

namespace ShopwareAdapter\ResponseParser\Order;

use Assert\Assertion;
use Assert\AssertionFailedException;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use Shopware\Models\Tax\Tax;
use ShopwareAdapter\DataProvider\Currency\CurrencyDataProviderInterface;
use ShopwareAdapter\DataProvider\Tax\TaxDataProviderInterface;
use ShopwareAdapter\ResponseParser\Address\AddressResponseParserInterface;
use ShopwareAdapter\ResponseParser\Customer\CustomerResponseParserInterface;
use ShopwareAdapter\ResponseParser\GetAttributeTrait;
use ShopwareAdapter\ResponseParser\OrderItem\OrderItemResponseParserInterface;
use ShopwareAdapter\ShopwareAdapter;
use SystemConnector\IdentityService\Exception\NotFoundException;
use SystemConnector\IdentityService\IdentityServiceInterface;
use SystemConnector\TransferObject\Currency\Currency;
use SystemConnector\TransferObject\Order\Comment\Comment;
use SystemConnector\TransferObject\Order\Order;
use SystemConnector\TransferObject\Order\OrderItem\OrderItem;
use SystemConnector\TransferObject\OrderStatus\OrderStatus;
use SystemConnector\TransferObject\PaymentMethod\PaymentMethod;
use SystemConnector\TransferObject\PaymentStatus\PaymentStatus;
use SystemConnector\TransferObject\ShippingProfile\ShippingProfile;
use SystemConnector\TransferObject\Shop\Shop;
use SystemConnector\TransferObject\VatRate\VatRate;

class OrderResponseParser implements OrderResponseParserInterface
{
    use GetAttributeTrait;

    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * @var OrderItemResponseParserInterface
     */
    private $orderItemResponseParser;

    /**
     * @var AddressResponseParserInterface
     */
    private $orderAddressParser;

    /**
     * @var CustomerResponseParserInterface
     */
    private $customerParser;

    /**
     * @var CurrencyDataProviderInterface
     */
    private $currencyDataProvider;

    /**
     * @var TaxDataProviderInterface
     */
    private $taxDataProvider;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * OrderResponseParser constructor.
     */
    public function __construct(
        IdentityServiceInterface $identityService,
        OrderItemResponseParserInterface $orderItemResponseParser,
        AddressResponseParserInterface $orderAddressParser,
        CustomerResponseParserInterface $customerParser,
        CurrencyDataProviderInterface $currencyDataProvider,
        TaxDataProviderInterface $taxDataProvider,
        LoggerInterface $logger
    ) {
        $this->identityService = $identityService;
        $this->orderItemResponseParser = $orderItemResponseParser;
        $this->orderAddressParser = $orderAddressParser;
        $this->customerParser = $customerParser;
        $this->currencyDataProvider = $currencyDataProvider;
        $this->taxDataProvider = $taxDataProvider;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(array $entry): array
    {
        if (!$this->isValidOrder($entry)) {
            return [];
        }

        $taxFree = $entry['taxFree'] || $entry['net'];

        $orderItems = array_filter(
            array_map(
                function (array $orderItem) use ($taxFree) {
                    return $this->orderItemResponseParser->parse($orderItem, $taxFree);
                },
                $entry['details']
            )
        );

        $orderItems[] = $this->getShippingCosts($entry);

        $billingAddress = $this->orderAddressParser->parse($entry['billing']);
        $shippingAddress = $this->orderAddressParser->parse($entry['shipping']);

        if (null === $billingAddress || null === $shippingAddress) {
            $this->logger->warning('could not parse address,  order: ' . $entry['number']);

            return [];
        }

        if (null === $entry['customer']) {
            $this->logger->warning('could not find customer,  order: ' . $entry['number']);

            return [];
        }

        $customer = $this->customerParser->parse($entry['customer']);

        if (null === $customer) {
            $this->logger->warning('could not parse customer,  order: ' . $entry['number']);

            return [];
        }

        $customer->setMobilePhoneNumber($billingAddress->getMobilePhoneNumber());
        $customer->setPhoneNumber($billingAddress->getPhoneNumber());

        $orderStatusIdentifier = $this->getConnectorIdentifier($entry['orderStatusId'], OrderStatus::TYPE);
        $paymentStatusIdentifier = $this->getConnectorIdentifier($entry['paymentStatusId'], PaymentStatus::TYPE);
        $paymentMethodIdentifier = $this->getConnectorIdentifier($entry['paymentId'], PaymentMethod::TYPE);
        $shippingProfileIdentifier = $this->getConnectorIdentifier($entry['dispatchId'], ShippingProfile::TYPE);
        $shopIdentifier = $this->getConnectorIdentifier($entry['languageSubShop']['id'], Shop::TYPE);

        $shopwareCurrencyIdentifier = $this->currencyDataProvider->getCurrencyIdentifierByCode($entry['currency']);
        $currencyIdentifier = $this->getConnectorIdentifier($shopwareCurrencyIdentifier, Currency::TYPE);

        $orderIdentity = $this->identityService->findOneOrCreate(
            (string) $entry['id'],
            ShopwareAdapter::NAME,
            Order::TYPE
        );

        $isMappedOrderIdentity = $this->identityService->isMappedIdentity(
            $orderIdentity->getObjectIdentifier(),
            $orderIdentity->getObjectType(),
            $orderIdentity->getAdapterName()
        );

        if ($isMappedOrderIdentity) {
            return [];
        }

        $order = new Order();
        $order->setIdentifier($orderIdentity->getObjectIdentifier());
        $order->setOrderNumber($entry['number']);
        $order->setOrderItems($orderItems);
        $order->setAttributes($this->getAttributes($entry['attribute']));
        $order->setBillingAddress($billingAddress);
        $order->setShippingAddress($shippingAddress);
        $order->setComments($this->getComments($entry));
        $order->setCustomer($customer);
        $order->setOrderTime(DateTimeImmutable::createFromMutable($entry['orderTime']));
        $order->setOrderStatusIdentifier($orderStatusIdentifier);
        $order->setPaymentStatusIdentifier($paymentStatusIdentifier);
        $order->setPaymentMethodIdentifier($paymentMethodIdentifier);
        $order->setShippingProfileIdentifier($shippingProfileIdentifier);
        $order->setCurrencyIdentifier($currencyIdentifier);
        $order->setShopIdentifier($shopIdentifier);

        return [$order];
    }

    private function isValidOrder(array $entry): bool
    {
        $shopIdentity = $this->identityService->findOneOrThrow(
            (string) $entry['languageSubShop']['id'],
            ShopwareAdapter::NAME,
            Shop::TYPE
        );

        $isMappedIdentity = $this->identityService->isMappedIdentity(
            $shopIdentity->getObjectIdentifier(),
            $shopIdentity->getObjectType(),
            $shopIdentity->getAdapterName()
        );

        if (!$isMappedIdentity) {
            return false;
        }

        if (empty($entry['billing'])) {
            $this->logger->warning('empty order billing address - order: ' . $entry['number']);

            return false;
        }

        if (empty($entry['details'])) {
            $this->logger->warning('empty order positions - order: ' . $entry['number']);

            return false;
        }

        if (empty($entry['shipping'])) {
            $this->logger->warning('empty order shipping address - order: ' . $entry['number']);

            return false;
        }

        $shippingProfileIdentity = $this->identityService->findOneBy([
            'adapterIdentifier' => (string) $entry['dispatchId'],
            'adapterName' => ShopwareAdapter::NAME,
            'objectType' => ShippingProfile::TYPE,
        ]);

        if (null === $shippingProfileIdentity) {
            $this->logger->warning('no shipping profile was selected for order: ' . $entry['number']);

            return false;
        }

        return true;
    }

    /**
     * @param array $entry
     *
     * @return Comment[]
     */
    private function getComments($entry): array
    {
        $comments = [];

        if ($entry['internalComment']) {
            $comment = new Comment();
            $comment->setType(Comment::TYPE_INTERNAL);
            $comment->setComment($entry['internalComment']);
            $comments[] = $comment;
        }

        if ($entry['customerComment']) {
            $comment = new Comment();
            $comment->setType(Comment::TYPE_CUSTOMER);
            $comment->setComment($entry['customerComment']);
            $comments[] = $comment;
        }

        return $comments;
    }

    /**
     * @param int    $entry
     * @param string $type
     *
     * @throws AssertionFailedException
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
     * @throws NotFoundException
     */
    private function getShippingCostsVatRateIdentifier(array $entry): string
    {
        if (null === $entry['invoiceShippingTaxRate']) {
            $entry['invoiceShippingTaxRate'] = 0.0;
        }

        /**
         * @var Tax $taxModel
         */
        $taxModel = $this->taxDataProvider->getTax((float) $entry['invoiceShippingTaxRate'], null);

        if (null === $taxModel) {
            $taxModel = $this->taxDataProvider->getTax((float) $entry['invoiceShippingTaxRate'], $entry['billing']['countryId']);
        }

        if (null === $taxModel) {
            throw new NotFoundException('no matching tax rate found - ' . $entry['invoiceShippingTaxRate']);
        }

        $taxRateId = $taxModel->getId();

        if (isset($entry['dispatch']['taxCalculation']) && $entry['dispatch']['taxCalculation'] > 0) {
            $taxRateId = $entry['dispatch']['taxCalculation'];
        }

        $identity = $this->identityService->findOneBy([
            'adapterIdentifier' => (string) $taxRateId,
            'adapterName' => ShopwareAdapter::NAME,
            'objectType' => VatRate::TYPE,
        ]);

        if (null === $identity) {
            throw new NotFoundException('missing vat rate identity for taxId - ' . $taxRateId);
        }

        return $identity->getObjectIdentifier();
    }

    /**
     * @throws NotFoundException
     */
    private function getShippingCosts(array $entry): OrderItem
    {
        $shippingCosts = $this->getShippingAmount($entry);
        $vatRateIdentifier = $this->getShippingCostsVatRateIdentifier($entry);

        $orderItem = new OrderItem();
        $orderItem->setType(OrderItem::TYPE_SHIPPING_COSTS);
        $orderItem->setQuantity(1.0);
        $orderItem->setName('ShippingCosts');
        $orderItem->setNumber('ShippingCosts');
        $orderItem->setPrice($shippingCosts);
        $orderItem->setVatRateIdentifier($vatRateIdentifier);

        return $orderItem;
    }

    private function getShippingAmount(array $entry): float
    {
        $isShippingBruttoAndNettoSame = 1 === $entry['taxFree'] && $entry['taxFree'] === $entry['net'];
        if ($isShippingBruttoAndNettoSame) {
            return $entry['invoiceShippingNet'] + $entry['invoiceShippingNet'] * $entry['invoiceShippingTaxRate'] / 100;
        }

        return (float) $entry['invoiceShipping'];
    }
}
