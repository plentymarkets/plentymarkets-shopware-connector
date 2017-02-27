<?php

namespace PlentymarketsAdapter\ServiceBus\CommandHandler\Order;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\ServiceBus\Command\CommandInterface;
use PlentyConnector\Connector\ServiceBus\Command\HandleCommandInterface;
use PlentyConnector\Connector\ServiceBus\Command\Order\HandleOrderCommand;
use PlentyConnector\Connector\ServiceBus\CommandHandler\CommandHandlerInterface;
use PlentyConnector\Connector\TransferObject\Country\Country;
use PlentyConnector\Connector\TransferObject\Language\Language;
use PlentyConnector\Connector\TransferObject\Order\Address\Address;
use PlentyConnector\Connector\TransferObject\Order\Comment\Comment;
use PlentyConnector\Connector\TransferObject\Order\Customer\Customer;
use PlentyConnector\Connector\TransferObject\Order\Order;
use PlentyConnector\Connector\TransferObject\Order\OrderItem\OrderItem;
use PlentyConnector\Connector\TransferObject\PaymentMethod\PaymentMethod;
use PlentyConnector\Connector\TransferObject\ShippingProfile\ShippingProfile;
use PlentyConnector\Connector\TransferObject\Shop\Shop;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
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
     * HandleOrderCommandHandler constructor.
     *
     * @param ClientInterface $client
     * @param IdentityServiceInterface $identityService
     */
    public function __construct(ClientInterface $client, IdentityServiceInterface $identityService)
    {
        $this->client = $client;
        $this->identityService = $identityService;
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

            $shippingProfileIdentity = $this->identityService->findOneBy([
                'objectIdentifier' => $order->getShippingProfileIdentifier(),
                'objectType' => ShippingProfile::TYPE,
                'adapterName' => PlentymarketsAdapter::NAME,
            ]);

            $params = [];


            /*
                Contact Options:
                int 	typeId 	The type ID of the contact option. It is possible to define individual contact option types. The following types are available by default and cannot be deleted:

                1 = Telephone
                2 = Email
                3 = Telefax
                4 = Web page
                5 = Marketplace
                6 = Identification number
                7 = Payment
                8 = User name
                9 = Group
                10 = Access
                11 = Additional

                int 	subTypeId 	The sub-type ID of the contact option. It is possible to define individual contact option sub-types. The following types are available by default and cannot be deleted:

                1 = Work
                2 = Mobile private
                3 = Mobile work
                4 = Private
                5 = PayPal
                6 = Ebay
                7 = Amazon
                8 = Klarna
                9 = DHL
                10 = Forum
                11 = Guest
                12 = Contact person
                13 = Marketplace partner
             */

            // create new order
            if ($order->getOrderType() === Order::TYPE_ORDER) {
                $params['typeId'] = 1;
            } else {
                // TODO: throw notice
            }

            $params['plentyId'] = $shopIdentity->getAdapterIdentifier();

            $languageIdentity = $this->identityService->findOneBy([
                'objectIdentifier' => $order->getCustomer()->getLanguageIdentifier(),
                'objectType' => Language::TYPE,
                'adapterName' => PlentymarketsAdapter::NAME,
            ]);

            $customer = $order->getCustomer();
            $plentyCustomer = false;

            if ($customer->getType() === Customer::TYPE_NORMAL) {
                $customerResult = $this->client->request('GET', 'accounts/contacts', ['contactEmail' => $customer->getEmail()]);

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
                return $store['storeIdentifier'] === $shopIdentity->getAdapterIdentifier();
            });

            if (!$plentyCustomer) {
                $customerParams = [
                    'number' => $customer->getNumber(), // freidefiniert
                    'typeId' => 1, // hartcoded für kunde
                    'firstName' => $customer->getFirstname(),
                    'lastName' => $customer->getLastname(),
                    'gender' => $customer->getSalutation() === Customer::SALUTATION_MR ? 'male' : 'female',
                    //'classId' => 1,  //mapping
                    'lang' => $languageIdentity->getAdapterIdentifier(),
                    'referrerId' => 1,
                    'singleAccess' => $customer->getType() === Customer::TYPE_GUEST,
                    'plentyId' => $accountWebStore['id'],
                    'userId' => 1,
                    'options' => [
                        /*[
                            'typeId' => 1,
                            'subTypeId' => 4,
                            'value' => $customer->getPhoneNumber(),
                            'priority' => 0
                        ],
                        [
                            'typeId' => 1,
                            'subTypeId' => 2,
                            'value' => $customer->getMobilePhoneNumber(),
                            'priority' => 0
                        ],*/
                        [
                            'typeId' => 2,
                            'subTypeId' => 4,
                            'value' => $customer->getEmail(),
                            'priority' => 0
                        ]
                    ]
                ];

                $plentyCustomer = $this->client->request('POST', 'accounts/contacts', $customerParams);
            }

            $params['relations'] = [
                [
                    'referenceType' => 'contact',
                    'referenceId' => $plentyCustomer['id'],
                    'relation' => 'receiver'
                ]
            ];

            $params['addressRelations'] = [];

            $billingAddress = $this->createAddress($order->getBillingAddress());
            if (!empty($billingAddress)) {
                $params['addressRelations'][] = [
                    'typeId' => 1,
                    'addressId' => $billingAddress['id'],
                ];
            }

            $shippingAddress = $this->createAddress($order->getShippingAddress());
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

            $params['dates'] = [
                [
                    'typeId' => 2,
                    'date' => $order->getOrderTime()->format(DATE_W3C)
                ]
            ];

            $params['orderItems'] = array_map(function (OrderItem $item) use ($shippingProfileIdentity) {
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

                // /rest/vat/locations/{locationId}/countries/{countryId}
                $itemParams['countryVatId'] = 1; // TODO: remove hardcoded
                $itemParams['vatField'] = 0; // TODO: remove hardcoded

                // Wenn currency != EUR, nur Währung EUR angeben (faktor beachten)
                $itemParams['amounts'] = [
                    [
                        'currency' => 'EUR', // TODO: remove hardcoded
                        'priceOriginalGross' => $item->getPrice(),
                    ]
                ];


                $itemParams['properties'] = [
                    [
                        'typeId' => 2,
                        'value' => $shippingProfileIdentity->getAdapterIdentifier(),
                    ]
                ];


                // Custom Products // aus merkmale
                $itemParams['orderProperties'] = [];


                return $itemParams;
            }, $order->getOrderItems());

            $result = $this->client->request('post', 'orders', $params);

            foreach ($order->getComments() as $comment) {
                $commentParams = [
                    'referenceType' => 'order',
                    'referenceValue' => $result['id'],
                    'text' => $comment->getComment(),
                    'isVisibleForContact' => $comment->getType() === Comment::TYPE_CUSTOMER
                ];

                if ($comment->getType() === Comment::TYPE_INTERNAL) {
                    $commentParams['userId'] = 1; // TODO: userId des rest benutzers auslesen?
                }

                $commentResult = $this->client->request('post', 'comments', $commentParams);
            }
        }

        // TODO update existing order


        return true;
    }

    private function getVariationIdFromNumber($number)
    {
        $variations = $this->client->request('GET', 'items/variations', ['numberExact' => $number]);

        if (!empty($variations)) {
            $variation = array_shift($variations);

            return $variation['id'];
        }

        return false;
    }

    /**
     * @param Address $address
     *
     * @return array
     */
    private function createAddress(Address $address)
    {
        $countryIdentity = $this->identityService->findOneBy([
            'objectIdentifier' => $address->getCountryIdentifier(),
            'objectType' => Country::TYPE,
            'adapterName' => PlentymarketsAdapter::NAME
        ]);

        if (null === $countryIdentity) {
            // TODO: decide what to do
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

        $params = [
            'name1' => trim($address->getCompany() . ' ' . $address->getDepartment()),
            'name2' => $address->getFirstname(),
            'name3' => $address->getLastname(),
            'address1' => $address1,
            'address2' => $address2,
            'address3' => $address3,
            'postalCode' => $address->getZipcode(),
            'town' => $address->getCity(),
            'countryId' => $countryIdentity->getAdapterIdentifier(),
        ];

        return $this->client->request('POST', 'accounts/addresses', $params);
    }
}
