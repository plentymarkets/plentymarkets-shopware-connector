<?php

namespace PlentymarketsAdapter\ServiceBus\CommandHandler\Order;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\ServiceBus\Command\CommandInterface;
use PlentyConnector\Connector\ServiceBus\Command\HandleCommandInterface;
use PlentyConnector\Connector\ServiceBus\Command\Order\HandleOrderCommand;
use PlentyConnector\Connector\ServiceBus\CommandHandler\CommandHandlerInterface;
use PlentyConnector\Connector\TransferObject\Country\Country;
use PlentyConnector\Connector\TransferObject\Currency\Currency;
use PlentyConnector\Connector\TransferObject\CustomerGroup\CustomerGroup;
use PlentyConnector\Connector\TransferObject\Language\Language;
use PlentyConnector\Connector\TransferObject\Order\Address\Address;
use PlentyConnector\Connector\TransferObject\Order\Comment\Comment;
use PlentyConnector\Connector\TransferObject\Order\Customer\Customer;
use PlentyConnector\Connector\TransferObject\Order\Order;
use PlentyConnector\Connector\TransferObject\Order\OrderItem\OrderItem;
use PlentyConnector\Connector\TransferObject\Order\Payment\Payment;
use PlentyConnector\Connector\TransferObject\Order\PaymentData\SepaPaymentData;
use PlentyConnector\Connector\TransferObject\PaymentMethod\PaymentMethod;
use PlentyConnector\Connector\TransferObject\ShippingProfile\ShippingProfile;
use PlentyConnector\Connector\TransferObject\Shop\Shop;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use Psr\Log\LoggerInterface;
use VIISON\AddressSplitter\AddressSplitter;

/**
 * Class HandleOrderCommandHandler.
 */
class HandleOrderCommandHandler implements CommandHandlerInterface
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * HandleOrderCommandHandler constructor.
     *
     * @param ClientInterface $client
     * @param IdentityServiceInterface $identityService
     * @param LoggerInterface $logger
     */
    public function __construct(
        ClientInterface $client,
        IdentityServiceInterface $identityService,
        LoggerInterface $logger
    ) {
        $this->client = $client;
        $this->identityService = $identityService;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(CommandInterface $command)
    {
        return $command instanceof HandleOrderCommand &&
            $command->getAdapterName() === PlentymarketsAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(CommandInterface $command)
    {
        /**
         * @var HandleCommandInterface $command
         * @var Order $order
         */
        $order = $command->getTransferObject();

        $identity = $this->identityService->findOneBy([
            'objectIdentifier' => $order->getIdentifier(),
            'objectType' => Order::TYPE,
            'adapterName' => PlentymarketsAdapter::NAME,
        ]);

        if ($identity === null) {
            $shopIdentity = $this->identityService->findOneBy([
                'objectIdentifier' => $order->getShopIdentifier(),
                'objectType' => Shop::TYPE,
                'adapterName' => PlentymarketsAdapter::NAME,
            ]);

            $languageIdentity = $this->identityService->findOneBy([
                'objectIdentifier' => $order->getCustomer()->getLanguageIdentifier(),
                'objectType' => Language::TYPE,
                'adapterName' => PlentymarketsAdapter::NAME,
            ]);

            $shippingProfileIdentity = $this->identityService->findOneBy([
                'objectIdentifier' => $order->getShippingProfileIdentifier(),
                'objectType' => ShippingProfile::TYPE,
                'adapterName' => PlentymarketsAdapter::NAME,
            ]);

            $currencyIdentity = $this->identityService->findOneBy([
                'objectIdentifier' => $order->getCurrencyIdentifier(),
                'objectType' => Currency::TYPE,
                'adapterName' => PlentymarketsAdapter::NAME,
            ]);

            if (null === $currencyIdentity) {
                return false;
            }

            if ('EUR' !== $currencyIdentity->getAdapterIdentifier()) {
                $this->logger->warning('only the currency EUR is currently supported', ['command', $command]);

                return false;
            }

            $params = [];

            if ($order->getOrderType() === Order::TYPE_ORDER) {
                $params['typeId'] = 1;
            } else {
                $this->logger->notice('only orders are supported');

                return false;
            }

            $params['plentyId'] = $shopIdentity->getAdapterIdentifier();

            $plentyCustomer = $this->createCustomer($order);

            $params['relations'] = [
                [
                    'referenceType' => 'contact',
                    'referenceId' => $plentyCustomer['id'],
                    'relation' => 'receiver',
                ],
            ];

            $params['addressRelations'] = [];

            $billingAddress = $this->createAddress($order->getBillingAddress(), $order->getCustomer(), $plentyCustomer);
            if (!empty($billingAddress)) {
                $params['addressRelations'][] = [
                    'typeId' => 1,
                    'addressId' => $billingAddress['id'],
                ];
            }

            $shippingAddress = $this->createAddress($order->getShippingAddress(), $order->getCustomer(),
                $plentyCustomer);
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
                // TODO: throw

                return false;
            }

            // TODO: properties https://developers.plentymarkets.com/rest-doc/order_order_property/details

            // TODO: COUPON_CODE = 18 & COUPON_TYPE = 19 type=fixed

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

            $params['orderItems'] = array_map(function (OrderItem $item) use (
                $shippingProfileIdentity,
                $currencyIdentity
            ) {
                $itemParams = [];

                if ($item->getType() === OrderItem::TYPE_PRODUCT) {
                    $typeId = 1;
                } elseif ($item->getType() === OrderItem::TYPE_DISCOUNT) {
                    $typeId = 4;
                } elseif ($item->getType() === OrderItem::TYPE_VOUCHER) {
                    $typeId = 4;
                } elseif ($item->getType() === OrderItem::TYPE_COUPON) {
                    $typeId = 5;
                } elseif ($item->getType() === OrderItem::TYPE_PAYMENT_SURCHARGE) {
                    $typeId = 7;
                } elseif ($item->getType() === OrderItem::TYPE_SHIPPING_COSTS) {
                    $typeId = 6;
                } else {
                    throw new \Exception('unsupported type');
                }

                $itemParams['typeId'] = $typeId;
                $itemParams['orderItemName'] = $item->getName();
                $itemParams['quantity'] = $item->getQuantity();
                $itemParams['shippingProfileId'] = $shippingProfileIdentity->getAdapterIdentifier();

                if (!empty($item->getNumber())) {
                    $itemParams['itemVariationId'] = $this->getVariationIdFromNumber($item->getNumber());
                } else {
                    $itemParams['itemVariationId'] = 0;
                }

                if ($item->getType() === OrderItem::TYPE_PRODUCT && null === $item->getNumber()) {
                    $itemParams['typeId'] = 9;
                }

                //$this->getVatConfiguration($order, $item);

                if (null !== $item->getVatRateIdentifier()) {
                    // /rest/vat/locations/{locationId}/countries/{countryId}
                    $itemParams['countryVatId'] = 1; // TODO: remove hardcoded
                    $itemParams['vatField'] = 0; // TODO: remove hardcoded
                } else {
                    $itemParams['countryVatId'] = 1; // TODO: remove hardcoded
                    $itemParams['vatRate'] = 0;
                }

                // Wenn currency != EUR, nur Währung EUR angeben (faktor beachten)

                $itemParams['amounts'] = [
                    [
                        'currency' => $currencyIdentity->getAdapterIdentifier(),
                        'priceOriginalGross' => $item->getPrice(),
                    ],
                ];

                $itemParams['properties'] = [
                    [
                        'typeId' => 2,
                        'value' => $shippingProfileIdentity->getAdapterIdentifier(),
                    ],
                ];

                // Custom Products // aus merkmale
                $itemParams['orderProperties'] = [];

                return $itemParams;
            }, $order->getOrderItems());

            $params['referrerId'] = 1; // TOOD: testen

            $this->logger->notice(json_encode($params));

            $result = $this->client->request('post', 'orders', $params);

            foreach ($order->getComments() as $comment) {
                $commentParams = [
                    'referenceType' => 'order',
                    'referenceValue' => $result['id'],
                    'text' => $comment->getComment(),
                    'isVisibleForContact' => $comment->getType() === Comment::TYPE_CUSTOMER,
                ];

                if ($comment->getType() === Comment::TYPE_INTERNAL) {
                    $commentParams['userId'] = 1; // TODO: userId des rest benutzers auslesen?
                }

                $this->client->request('post', 'comments', $commentParams);
            }

            foreach ($order->getPaymentData() as $paymentData) {
                if ($paymentData instanceof SepaPaymentData) {
                    $sepaPaymentDataParams = [
                        'lastUpdateBy' => 'import',
                        'accountOwner' => $paymentData->getAccountOwner(),
                        'iban' => $paymentData->getIban(),
                        'bic' => $paymentData->getBic(),
                        'orderId' => $result['id'],
                        'contactId' => $plentyCustomer['id'],
                    ];

                    $this->client->request('POST', 'accounts/contacts/banks', $sepaPaymentDataParams);
                }
            }

            foreach ($order->getPayments() as $payment) {
                $plentyOrderIdentifier = $result['id'];

                $this->handlePayment($payment, $order, $billingAddress, $shippingAddress, $plentyOrderIdentifier);
            }

            $this->identityService->create(
                $order->getIdentifier(),
                Order::TYPE,
                (string) $result['id'],
                PlentymarketsAdapter::NAME
            );
        }

        // TODO update existing order

        return true;
    }

    /**
     * @param $number
     *
     * @return int
     */
    private function getVariationIdFromNumber($number)
    {
        $variations = $this->client->request('GET', 'items/variations', ['numberExact' => $number]);

        if (!empty($variations)) {
            $variation = array_shift($variations);

            return $variation['id'];
        }

        return 0;
    }

    /**
     * @param Order $order
     *
     * @return array|bool|mixed
     */
    private function createCustomer(Order $order)
    {
        $languageIdentity = $this->identityService->findOneBy([
            'objectIdentifier' => $order->getCustomer()->getLanguageIdentifier(),
            'objectType' => Language::TYPE,
            'adapterName' => PlentymarketsAdapter::NAME,
        ]);

        $shopIdentity = $this->identityService->findOneBy([
            'objectIdentifier' => $order->getShopIdentifier(),
            'objectType' => Shop::TYPE,
            'adapterName' => PlentymarketsAdapter::NAME,
        ]);

        $customer = $order->getCustomer();
        $plentyCustomer = false;

        if ($customer->getType() === Customer::TYPE_NORMAL) {
            $customerResult = $this->client->request('GET', 'accounts/contacts', [
                'contactEmail' => $customer->getEmail(),
            ]);

            if (!empty($customerResult)) {
                $possibleCustomers = array_filter($customerResult, function ($entry) {
                    return !isset($entry['singleAccess']) || $entry['singleAccess'] === false;
                });

                $plentyCustomer = array_shift($possibleCustomers);
            }
        }

        static $webstores;

        if (null === $webstores) {
            $webstores = $this->client->request('GET', 'webstores');
        }

        $accountWebStore = array_filter($webstores, function ($store) use ($shopIdentity) {
            return (string) $store['storeIdentifier'] === $shopIdentity->getAdapterIdentifier();
        });

        if (empty($accountWebStore)) {
            // TODO: throw
        }

        $accountWebStore = array_shift($accountWebStore);

        $customerGroupIdentitiy = $this->identityService->findOneBy([
            'objectIdentifier' => $order->getCustomer()->getCustomerGroupIdentifier(),
            'objectType' => CustomerGroup::TYPE,
            'adapterName' => PlentymarketsAdapter::NAME,
        ]);

        $customerParams = [
            'number' => $customer->getNumber(),
            'typeId' => 1, // hartcoded für kunde
            'firstName' => $customer->getFirstname(),
            'lastName' => $customer->getLastname(),
            'gender' => $customer->getSalutation() === Customer::SALUTATION_MR ? 'male' : 'female',
            'lang' => $languageIdentity->getAdapterIdentifier(),
            'referrerId' => 1, // TODO: Konfigurierbar über Config. (/rest/orders/referrers)
            'singleAccess' => $customer->getCustomerType() === Customer::TYPE_GUEST,
            'plentyId' => $accountWebStore['id'],
            'newsletterAllowanceAt' => '',
            'lastOrderAt' => $order->getOrderTime()->format(DATE_W3C),
            'userId' => 1, // TODO: Konfigurierbar über Config (rest/accounts)
            'options' => [],
        ];

        // TODO: handle no customer group at plenty
        if (null !== $customerGroupIdentitiy) {
            $customerParams['classId'] = (int) $customerGroupIdentitiy->getAdapterIdentifier();
        }

        if (null !== $customer->getBirthday()) {
            $customerParams['birthdayAt'] = $customer->getBirthday()->format(DATE_W3C);
        }

        if (!empty($customer->getPhoneNumber())) {
            $customerParams['options'][] = [
                'typeId' => 1,
                'subTypeId' => 4,
                'value' => $customer->getPhoneNumber(),
                'priority' => 0,
            ];
        }

        if (!empty($customer->getMobilePhoneNumber())) {
            $customerParams['options'][] = [
                'typeId' => 1,
                'subTypeId' => 2,
                'value' => $customer->getMobilePhoneNumber(),
                'priority' => 0,
            ];
        }

        if (!empty($customer->getEmail())) {
            $customerParams['options'][] = [
                'typeId' => 2,
                'subTypeId' => 4,
                'value' => $customer->getEmail(),
                'priority' => 0,
            ];
        }

        if (!$plentyCustomer) {
            $plentyCustomer = $this->client->request('POST', 'accounts/contacts', $customerParams);
        } else {
            $this->client->request('PUT', 'accounts/contacts/' . $plentyCustomer['id'], $customerParams);
        }

        return $plentyCustomer;
    }

    /**
     * @param Address $address
     * @param Customer $customer
     * @param array $plentyCustomer
     *
     * @throws \Exception
     *
     * @return array
     */
    private function createAddress(Address $address, Customer $customer, array $plentyCustomer)
    {
        $countryIdentity = $this->identityService->findOneBy([
            'objectIdentifier' => $address->getCountryIdentifier(),
            'objectType' => Country::TYPE,
            'adapterName' => PlentymarketsAdapter::NAME,
        ]);

        if (null === $countryIdentity) {
            throw new \Exception('unmapped country');
        }

        try {
            $splitResult = AddressSplitter::splitAddress($address->getStreet());

            $address1 = $splitResult['streetName'];
            $address2 = $splitResult['houseNumber'];
            $address3 = trim($splitResult['additionToAddress1'] . ' ' . $splitResult['additionToAddress2']);
        } catch (\Exception $exception) {
            $address1 = $address->getStreet();
            $address2 = '';
            $address3 = '';
        }

        // TODO: Addition feld prüfen

        $params = [
            'name1' => trim($address->getCompany() . ' ' . $address->getDepartment()),
            'name2' => $address->getFirstname(),
            'name3' => $address->getLastname(),
            'address1' => $address1,
            'address2' => $address2,
            'address3' => $address3,
            'postalCode' => $address->getPostalCode(),
            'town' => $address->getCity(),
            'countryId' => $countryIdentity->getAdapterIdentifier(),
            'options' => [
                [
                    'typeId' => 5,
                    'value' => $customer->getEmail(),
                ],
                [
                    'typeId' => 4,
                    'value' => $customer->getPhoneNumber(),
                ],
            ],
        ];

        return $this->client->request('POST', 'accounts/contacts/' . $plentyCustomer['id'] . '/addresses', $params);
    }

    /**
     * @param Payment $payment
     * @param Order $order
     * @param null|array $billingAddress
     * @param null|array $shippingAddress
     * @param $plentyOrderIdentifier
     *
     * @throws \Exception
     */
    private function handlePayment(
        Payment $payment,
        Order $order,
        array $billingAddress = null,
        array $shippingAddress = null,
        $plentyOrderIdentifier
    ) {
        $paymentMethodIdentity = $this->identityService->findOneBy([
            'objectIdentifier' => $payment->getPaymentMethodIdentifier(),
            'objectType' => PaymentMethod::TYPE,
            'adapterName' => PlentymarketsAdapter::NAME,
        ]);

        if (null === $paymentMethodIdentity) {
            $this->logger->error('payment method not mapped', ['payment' => $payment]);

            throw new \Exception('payment method not mapped');
        }

        $paymentMethodIdentity = $this->identityService->findOneBy([
            'objectIdentifier' => $payment->getPaymentMethodIdentifier(),
            'objectType' => PaymentMethod::TYPE,
            'adapterName' => PlentymarketsAdapter::NAME,
        ]);

        $currencyIdentity = $this->identityService->findOneBy([
            'objectIdentifier' => $payment->getCurrencyIdentifier(),
            'objectType' => Currency::TYPE,
            'adapterName' => PlentymarketsAdapter::NAME,
        ]);

        if (null === $currencyIdentity) {
            $this->logger->error('currency not mapped', ['payment' => $payment]);

            throw new \Exception('currency not mapped');
        }

        $paymentParams = [
            'amount' => $payment->getAmount(),
            'exchangeRatio' => 1,
            'mopId' => $paymentMethodIdentity->getAdapterIdentifier(),
            'currency' => $currencyIdentity->getAdapterIdentifier(),
            'type' => 'credit',
            'transactionType' => 2,
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
                'typeId' => 11,
                'value' => $order->getCustomer()->getFirstname() . ' ' . $order->getCustomer()->getLastname(),
            ],
            [
                'typeId' => 12,
                'value' => $order->getCustomer()->getEmail(),
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

        if (null !== $billingAddress) {
            $paymentParams['property'][] = [
                'typeId' => 24,
                'value' => $billingAddress['id'],
            ];
        }

        if (null !== $shippingAddress) {
            $paymentParams['property'][] = [
                'typeId' => 25,
                'value' => $billingAddress['id'],
            ];
        }

        $paymentResult = $this->client->request('POST', 'payments', $paymentParams);

        $url = 'payment/' . $paymentResult['id'] . '/order/' . $plentyOrderIdentifier;
        $this->client->request('POST', $url);
    }
}
