<?php

namespace PlentymarketsAdapter\RequestGenerator\Order;

use PlentyConnector\Connector\ConfigService\ConfigServiceInterface;
use PlentyConnector\Connector\IdentityService\Exception\NotFoundException;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\Language\Language;
use PlentyConnector\Connector\TransferObject\Order\Address\Address;
use PlentyConnector\Connector\TransferObject\Order\Customer\Customer;
use PlentyConnector\Connector\TransferObject\Order\Order;
use PlentyConnector\Connector\TransferObject\Order\OrderItem\OrderItem;
use PlentyConnector\Connector\TransferObject\PaymentMethod\PaymentMethod;
use PlentyConnector\Connector\TransferObject\Shop\Shop;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\RequestGenerator\Order\Address\AddressRequestGeneratorInterface;
use PlentymarketsAdapter\RequestGenerator\Order\Customer\CustomerRequestGeneratorInterface;
use PlentymarketsAdapter\RequestGenerator\Order\OrderItem\OrderItemRequestGeneratorInterface;
use RuntimeException;

/**
 * Class OrderRequestGenerator
 */
class OrderRequestGenerator implements OrderRequestGeneratorInterface
{
    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var OrderItemRequestGeneratorInterface
     */
    private $orderItemRequestGenerator;

    /**
     * @var CustomerRequestGeneratorInterface
     */
    private $customerRequestGenerator;

    /**
     * @var AddressRequestGeneratorInterface
     */
    private $addressReuqestGenerator;

    /**
     * @var ConfigServiceInterface
     */
    private $config;

    /**
     * OrderRequestGenerator constructor.
     *
     * @param IdentityServiceInterface $identityService
     * @param ClientInterface $client
     * @param OrderItemRequestGeneratorInterface $orderItemRequestGenerator
     * @param CustomerRequestGeneratorInterface $customerRequestGenerator
     * @param AddressRequestGeneratorInterface $addressReuqestGenerator
     * @param ConfigServiceInterface $config
     */
    public function __construct(
        IdentityServiceInterface $identityService,
        ClientInterface $client,
        OrderItemRequestGeneratorInterface $orderItemRequestGenerator,
        CustomerRequestGeneratorInterface $customerRequestGenerator,
        AddressRequestGeneratorInterface $addressReuqestGenerator,
        ConfigServiceInterface $config
    ) {
        $this->identityService = $identityService;
        $this->client = $client;
        $this->orderItemRequestGenerator = $orderItemRequestGenerator;
        $this->customerRequestGenerator = $customerRequestGenerator;
        $this->addressReuqestGenerator = $addressReuqestGenerator;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(Order $order)
    {
        if ($order->getOrderType() !== Order::TYPE_ORDER) {
            throw new RuntimeException('Unsupported order type');
        }

        $shopIdentity = $this->identityService->findOneBy([
            'objectIdentifier' => $order->getShopIdentifier(),
            'objectType' => Shop::TYPE,
            'adapterName' => PlentymarketsAdapter::NAME,
        ]);

        if (null === $shopIdentity) {
            throw new NotFoundException('shop not mapped');
        }

        $languageIdentity = $this->identityService->findOneBy([
            'objectIdentifier' => $order->getCustomer()->getLanguageIdentifier(),
            'objectType' => Language::TYPE,
            'adapterName' => PlentymarketsAdapter::NAME,
        ]);

        if (null === $languageIdentity) {
            throw new NotFoundException('language not mapped');
        }

        $params = [
            'typeId' => 1,
            'plentyId' => $shopIdentity->getAdapterIdentifier(),
        ];

        $plentyCustomer = $this->handleCustomer($order);

        $params['relations'] = [
            [
                'referenceType' => 'contact',
                'referenceId' => $plentyCustomer['id'],
                'relation' => 'receiver',
            ],
        ];

        $params['addressRelations'] = [];

        $billingAddress = $this->createAddress($order->getBillingAddress(), $order, $plentyCustomer, 1);
        if (!empty($billingAddress)) {
            $params['addressRelations'][] = [
                'typeId' => 1,
                'addressId' => $billingAddress['id'],
            ];
        }

        $shippingAddress = $this->createAddress($order->getShippingAddress(), $order, $plentyCustomer, 2);
        if (!empty($shippingAddress)) {
            $params['addressRelations'][] = [
                'typeId' => 2,
                'addressId' => $shippingAddress['id'],
            ];
        }

        $paymentMethodIdentity = $this->identityService->findOneBy([
            'objectIdentifier' => $order->getPaymentMethodIdentifier(),
            'objectType' => PaymentMethod::TYPE,
            'adapterName' => PlentymarketsAdapter::NAME,
        ]);

        if (null === $paymentMethodIdentity) {
            throw new NotFoundException('missing payment method mapping');
        }

        $params['properties'] = [
            [
                'typeId' => 6,
                'value' => $languageIdentity->getAdapterIdentifier(),
            ],
            [
                'typeId' => 7,
                'value' => $order->getOrderNumber(),
            ],
            [
                'typeId' => 3,
                'value' => $paymentMethodIdentity->getAdapterIdentifier(),
            ],
        ];

        $vouchers = array_filter($order->getOrderItems(), function (OrderItem $item) {
            return $item->getType() === OrderItem::TYPE_VOUCHER;
        });

        $voucher = null;
        if (!empty($vouchers)) {
            /**
             * @var OrderItem $voucher
             */
            $voucher = array_shift($vouchers);
        }

        if (null !== $voucher) {
            $params['properties'][] = [
                'typeId' => 18,
                'value' => $voucher->getNumber(),
            ];

            $params['properties'][] = [
                'typeId' => 19,
                'value' => 'fixed',
            ];
        }

        $params['dates'] = [
            [
                'typeId' => 2,
                'date' => $order->getOrderTime()->format(DATE_W3C),
            ],
        ];

        $params['orderItems'] = [];
        foreach ($order->getOrderItems() as $orderItem) {
            $params['orderItems'][] = $this->orderItemRequestGenerator->generate($orderItem, $order);
        }

        $params['referrerId'] = $this->config->get('order_origin', 1);

        return $params;
    }

    /**
     * @param Address $address
     * @param Order   $order
     * @param array   $plentyCustomer
     * @param int     $addressType
     *
     * @return array
     */
    private function createAddress(Address $address, Order $order, array $plentyCustomer, $addressType = 1)
    {
        $params = $this->addressReuqestGenerator->generate($address, $order, $addressType);

        return $this->client->request('POST', 'accounts/contacts/' . $plentyCustomer['id'] . '/addresses', $params);
    }

    /**
     * @param string $mail
     *
     * @return null|array
     */
    private function findCustomer($mail)
    {
        $customerResult = $this->client->request('GET', 'accounts/contacts', [
            'contactEmail' => $mail,
        ]);

        if (empty($customerResult)) {
            return null;
        }

        $possibleCustomers = array_filter($customerResult, function ($entry) {
            return !isset($entry['singleAccess']) || $entry['singleAccess'] === false;
        });

        if (empty($possibleCustomers)) {
            return null;
        }

        return array_shift($possibleCustomers);
    }

    /**
     * @param Order $order
     *
     * @return array|bool|mixed
     */
    private function handleCustomer(Order $order)
    {
        $customer = $order->getCustomer();

        $plentyCustomer = false;

        if ($customer->getType() === Customer::TYPE_NORMAL) {
            $plentyCustomer = $this->findCustomer($customer->getEmail());
        }

        $customerParams = $this->customerRequestGenerator->generate($customer, $order);

        if (!$plentyCustomer) {
            $plentyCustomer = $this->client->request('POST', 'accounts/contacts', $customerParams);
        } else {
            $this->client->request('PUT', 'accounts/contacts/' . $plentyCustomer['id'], $customerParams);
        }

        return $plentyCustomer;
    }
}
