<?php

namespace PlentymarketsAdapter\RequestGenerator\Order;

use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\RequestGenerator\Order\Address\AddressRequestGeneratorInterface;
use PlentymarketsAdapter\RequestGenerator\Order\Customer\CustomerRequestGeneratorInterface;
use PlentymarketsAdapter\RequestGenerator\Order\OrderItem\OrderItemRequestGeneratorInterface;
use SystemConnector\IdentityService\Exception\NotFoundException;
use SystemConnector\IdentityService\IdentityServiceInterface;
use SystemConnector\TransferObject\Language\Language;
use SystemConnector\TransferObject\Order\Address\Address;
use SystemConnector\TransferObject\Order\Customer\Customer;
use SystemConnector\TransferObject\Order\Order;
use SystemConnector\TransferObject\Order\OrderItem\OrderItem;
use SystemConnector\TransferObject\PaymentMethod\PaymentMethod;
use SystemConnector\TransferObject\ShippingProfile\ShippingProfile;
use SystemConnector\TransferObject\Shop\Shop;

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

    public function __construct(
        IdentityServiceInterface $identityService,
        ClientInterface $client,
        OrderItemRequestGeneratorInterface $orderItemRequestGenerator,
        CustomerRequestGeneratorInterface $customerRequestGenerator,
        AddressRequestGeneratorInterface $addressReuqestGenerator
    ) {
        $this->identityService = $identityService;
        $this->client = $client;
        $this->orderItemRequestGenerator = $orderItemRequestGenerator;
        $this->customerRequestGenerator = $customerRequestGenerator;
        $this->addressReuqestGenerator = $addressReuqestGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(Order $order)
    {
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

        $shippingProfileIdentity = $this->identityService->findOneBy([
            'objectIdentifier' => $order->getShippingProfileIdentifier(),
            'objectType' => ShippingProfile::TYPE,
            'adapterName' => PlentymarketsAdapter::NAME,
        ]);

        if (null === $shippingProfileIdentity) {
            throw new NotFoundException('shipping profile not mapped');
        }

        $params['properties'] = [
            [
                'typeId' => 6,
                'value' => (string) $languageIdentity->getAdapterIdentifier(),
            ],
            [
                'typeId' => 7,
                'value' => (string) $order->getOrderNumber(),
            ],
            [
                'typeId' => 3,
                'value' => (string) $paymentMethodIdentity->getAdapterIdentifier(),
            ],
            [
                'typeId' => 2,
                'value' => (string) $shippingProfileIdentity->getAdapterIdentifier(),
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
                'value' => (string) $voucher->getNumber(),
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
            return $entry['singleAccess'] !== '1';
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
