<?php

namespace ShopwareAdapter\ResponseParser\Order;

use Assert\Assertion;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use PlentyConnector\Connector\IdentityService\Exception\NotFoundException;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\Currency\Currency;
use PlentyConnector\Connector\TransferObject\Order\Comment\Comment;
use PlentyConnector\Connector\TransferObject\Order\Order;
use PlentyConnector\Connector\TransferObject\Order\OrderItem\OrderItem;
use PlentyConnector\Connector\TransferObject\OrderStatus\OrderStatus;
use PlentyConnector\Connector\TransferObject\PaymentMethod\PaymentMethod;
use PlentyConnector\Connector\TransferObject\PaymentStatus\PaymentStatus;
use PlentyConnector\Connector\TransferObject\ShippingProfile\ShippingProfile;
use PlentyConnector\Connector\TransferObject\Shop\Shop;
use PlentyConnector\Connector\TransferObject\VatRate\VatRate;
use Psr\Log\LoggerInterface;
use Shopware\Models\Tax\Repository;
use Shopware\Models\Tax\Tax;
use ShopwareAdapter\DataProvider\Currency\CurrencyDataProviderInterface;
use ShopwareAdapter\ResponseParser\Address\AddressResponseParserInterface;
use ShopwareAdapter\ResponseParser\Customer\CustomerResponseParserInterface;
use ShopwareAdapter\ResponseParser\GetAttributeTrait;
use ShopwareAdapter\ResponseParser\OrderItem\OrderItemResponseParserInterface;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class OrderResponseParser
 */
class OrderResponseParser implements OrderResponseParserInterface
{
    use GetAttributeTrait;

    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * OrderResponseParser constructor.
     *
     * @param IdentityServiceInterface         $identityService
     * @param EntityManagerInterface           $entityManager
     * @param OrderItemResponseParserInterface $orderItemResponseParser
     * @param AddressResponseParserInterface   $orderAddressParser
     * @param CustomerResponseParserInterface  $customerParser
     * @param CurrencyDataProviderInterface    $currencyDataProvider
     * @param LoggerInterface                  $logger
     */
    public function __construct(
        IdentityServiceInterface $identityService,
        EntityManagerInterface $entityManager,
        OrderItemResponseParserInterface $orderItemResponseParser,
        AddressResponseParserInterface $orderAddressParser,
        CustomerResponseParserInterface $customerParser,
        CurrencyDataProviderInterface $currencyDataProvider,
        LoggerInterface $logger
    ) {
        $this->identityService         = $identityService;
        $this->entityManager           = $entityManager;
        $this->orderItemResponseParser = $orderItemResponseParser;
        $this->orderAddressParser      = $orderAddressParser;
        $this->customerParser          = $customerParser;
        $this->currencyDataProvider    = $currencyDataProvider;
        $this->logger                  = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(array $entry)
    {
        if (!$this->isValidOrder($entry)) {
            return [];
        }

        $taxFree = ($entry['taxFree']);

        $orderItems = array_filter(array_map(function (array $orderItem) use ($taxFree) {
            return $this->orderItemResponseParser->parse($orderItem, $taxFree);
        }, $this->prepareOrderItems($entry['details'], (bool) $entry['net'])));

        $orderItems[] = $this->getShippingCosts($entry, $taxFree);

        $billingAddress  = $this->orderAddressParser->parse($entry['billing']);
        $shippingAddress = $this->orderAddressParser->parse($entry['shipping']);

        if (null === $billingAddress || null === $shippingAddress) {
            $this->logger->warning('could not parse address,  order: ' . $entry['number']);

            return [];
        }

        $customer = $this->customerParser->parse($entry['customer']);

        if (null === $customer) {
            $this->logger->warning('could not parse customer,  order: ' . $entry['number']);

            return [];
        }

        $customer->setMobilePhoneNumber($billingAddress->getMobilePhoneNumber());
        $customer->setPhoneNumber($billingAddress->getPhoneNumber());

        $orderStatusIdentifier     = $this->getConnectorIdentifier($entry['orderStatusId'], OrderStatus::TYPE);
        $paymentStatusIdentifier   = $this->getConnectorIdentifier($entry['paymentStatusId'], PaymentStatus::TYPE);
        $paymentMethodIdentifier   = $this->getConnectorIdentifier($entry['paymentId'], PaymentMethod::TYPE);
        $shippingProfileIdentifier = $this->getConnectorIdentifier($entry['dispatchId'], ShippingProfile::TYPE);
        $shopIdentifier            = $this->getConnectorIdentifier($entry['languageSubShop']['id'], Shop::TYPE);

        $shopwareCurrencyIdentifier = $this->currencyDataProvider->getCurrencyIdentifierByCode($entry['currency']);
        $currencyIdentifier         = $this->getConnectorIdentifier($shopwareCurrencyIdentifier, Currency::TYPE);

        $orderIdentity = $this->identityService->findOneOrCreate(
            (string) $entry['id'],
            ShopwareAdapter::NAME,
            Order::TYPE
        );

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
        $order->setOrderType(Order::TYPE_ORDER);
        $order->setOrderStatusIdentifier($orderStatusIdentifier);
        $order->setPaymentStatusIdentifier($paymentStatusIdentifier);
        $order->setPaymentMethodIdentifier($paymentMethodIdentifier);
        $order->setShippingProfileIdentifier($shippingProfileIdentifier);
        $order->setCurrencyIdentifier($currencyIdentifier);
        $order->setShopIdentifier($shopIdentifier);

        return [$order];
    }

    /**
     * @param array $entry
     *
     * @return bool
     */
    private function isValidOrder(array $entry)
    {
        $shopIdentity = $this->identityService->findOneOrThrow(
            (string) $entry['languageSubShop']['id'],
            ShopwareAdapter::NAME,
            Shop::TYPE
        );

        $isMappedIdentity = $this->identityService->isMapppedIdentity(
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

        if (empty($entry['shipping'])) {
            $this->logger->warning('empty order shipping address - order: ' . $entry['number']);

            return false;
        }

        $shippingProfileIdentity = $this->identityService->findOneBy([
            'adapterIdentifier' => (string) $entry['dispatchId'],
            'adapterName'       => ShopwareAdapter::NAME,
            'objectType'        => ShippingProfile::TYPE,
        ]);

        if (null === $shippingProfileIdentity) {
            $this->logger->warning('no shipping profile was selected for order: ' . $entry['number']);

            return false;
        }

        return true;
    }

    /**
     * @param array $orderItems
     * @param bool  $isNet
     *
     * @return array
     */
    private function prepareOrderItems(array $orderItems, $isNet)
    {
        foreach ($orderItems as $key => &$orderItem) {
            if (empty($orderItem['taxId'])) {
                if (empty($orderItem['taxRate'])) {
                    continue;
                }

                /**
                 * @var Repository $repository
                 */
                $repository = $this->entityManager->getRepository(Tax::class);

                /**
                 * @var Tax $taxModel
                 */
                $taxModel = $repository->findOneBy(['tax' => $orderItem['taxRate']]);

                if (null === $taxModel) {
                    throw new InvalidArgumentException('no matching tax rate found - ' . $orderItem['taxRate']);
                }

                $orderItems[$key]['taxId'] = $taxModel->getId();
            }

            if ($isNet) {
                $priceNet           = $orderItem['price'];
                $orderItem['price'] = $priceNet + (($priceNet / 100) * $orderItem['taxRate']);
            }
        }

        return $orderItems;
    }

    /**
     * @param array $entry
     *
     * @return Comment[]
     */
    private function getComments(array $entry)
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
     * @return string
     */
    private function getConnectorIdentifier($entry, $type)
    {
        Assertion::integerish($entry);

        return $this->identityService->findOneOrThrow(
            (string) $entry,
            ShopwareAdapter::NAME,
            $type
        )->getObjectIdentifier();
    }

    /**
     * @param array $entry
     *
     * @return string|null
     */
    private function getShippingCostsVatRateIdentifier(array $entry)
    {
        if (!isset($entry['dispatch']['taxCalculation'])) {
            return null;
        }

        if ($entry['dispatch']['taxCalculation'] > 0) {
            $identity = $this->identityService->findOneBy([
                'adapterIdentifier' => (string) $entry['dispatch']['taxCalculation'],
                'adapterName'       => ShopwareAdapter::NAME,
                'objectType'        => VatRate::TYPE,
            ]);

            if (null === $identity) {
                throw new NotFoundException('tax rate of shipping costs not found - ' . $entry['dispatch']['taxCalculation']);
            }

            return $identity->getObjectIdentifier();
        }

        $maxTaxRate           = 0;
        $maxTaxRateIdentifier = 0;

        foreach ($entry['details'] as $orderItem) {
            if (empty($orderItem['taxId'])) {
                continue;
            }

            if ($orderItem['taxRate'] < $maxTaxRate) {
                continue;
            }

            $maxTaxRate           = $orderItem['taxRate'];
            $maxTaxRateIdentifier = $orderItem['taxId'];
        }

        $identity = $this->identityService->findOneBy([
            'adapterIdentifier' => (string) $maxTaxRateIdentifier,
            'adapterName'       => ShopwareAdapter::NAME,
            'objectType'        => VatRate::TYPE,
        ]);

        if (null === $identity) {
            throw new NotFoundException('missing tax rate mapping - ' . $maxTaxRateIdentifier);
        }

        return $identity->getObjectIdentifier();
    }

    /**
     * @param array $entry
     * @param bool  $taxFree
     *
     * @return OrderItem
     */
    private function getShippingCosts(array $entry, $taxFree = false)
    {
        if ($taxFree) {
            $shippingCosts     = (float) $entry['invoiceShippingNet'];
            $vatRateIdentifier = null;
        } else {
            $shippingCosts     = (float) $entry['invoiceShipping'];
            $vatRateIdentifier = $this->getShippingCostsVatRateIdentifier($entry);
        }

        $orderItem = new OrderItem();
        $orderItem->setType(OrderItem::TYPE_SHIPPING_COSTS);
        $orderItem->setQuantity(1.0);
        $orderItem->setName('ShippingCosts');
        $orderItem->setNumber('ShippingCosts');
        $orderItem->setPrice($shippingCosts);
        $orderItem->setVatRateIdentifier($vatRateIdentifier);

        return $orderItem;
    }
}
