<?php

namespace PlentymarketsAdapter\ResponseParser\Order;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\Country\Country;
use PlentyConnector\Connector\TransferObject\Currency\Currency;
use PlentyConnector\Connector\TransferObject\CustomerGroup\CustomerGroup;
use PlentyConnector\Connector\TransferObject\Language\Language;
use PlentyConnector\Connector\TransferObject\Order\Address\Address;
use PlentyConnector\Connector\TransferObject\Order\Comment\Comment;
use PlentyConnector\Connector\TransferObject\Order\Customer\Customer;
use PlentyConnector\Connector\TransferObject\Order\Order;
use PlentyConnector\Connector\TransferObject\Order\OrderItem\OrderItem;
use PlentyConnector\Connector\TransferObject\Order\Package\Package;
use PlentyConnector\Connector\TransferObject\OrderStatus\OrderStatus;
use PlentyConnector\Connector\TransferObject\PaymentMethod\PaymentMethod;
use PlentyConnector\Connector\TransferObject\PaymentStatus\PaymentStatus;
use PlentyConnector\Connector\TransferObject\ShippingProfile\ShippingProfile;
use PlentyConnector\Connector\TransferObject\Shop\Shop;
use PlentyConnector\Connector\ValueObject\Identity\Identity;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ReadApi\Address\Address as AddressApi;
use PlentymarketsAdapter\ReadApi\Comment\Comment as CommentApi;
use PlentymarketsAdapter\ReadApi\Customer\Customer as CustomerApi;
use PlentymarketsAdapter\ReadApi\Webstore as WebstoreApi;
use Psr\Log\LoggerInterface;

/**
 * Class OrderResponseParser
 */
class OrderResponseParser implements OrderResponseParserInterface
{
    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var AddressApi
     */
    private $addressApi;

    /**
     * @var CustomerApi
     */
    private $customerApi;

    /**
     * @var CommentApi
     */
    private $commentApi;

    /**
     * @var WebstoreApi
     */
    private $webstoreApi;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * OrderResponseParser constructor.
     *
     * @param IdentityServiceInterface $identityService
     * @param LoggerInterface $logger
     * @param AddressApi $addressApi
     * @param CustomerApi $customerApi
     * @param CommentApi $commentApi
     * @param WebstoreApi $webstoreApi
     * @param ClientInterface $client
     */
    public function __construct(
        IdentityServiceInterface $identityService,
        LoggerInterface $logger,
        AddressApi $addressApi,
        CustomerApi $customerApi,
        CommentApi $commentApi,
        WebstoreApi $webstoreApi,
        ClientInterface $client
    ) {
        $this->identityService = $identityService;
        $this->logger = $logger;
        $this->addressApi = $addressApi;
        $this->customerApi = $customerApi;
        $this->commentApi = $commentApi;
        $this->webstoreApi = $webstoreApi;
        $this->client = $client;
    }

    /**
     * TODO: OrderItems
     * TODO: Comments
     *
     * {@inheritdoc}
     */
    public function parse(array $entry)
    {
        $shopIdentity = $this->identityService->findOneBy([
            'adapterIdentifier' => (string) $entry['plentyId'],
            'adapterName' => PlentymarketsAdapter::NAME,
            'objectType' => Shop::TYPE,
        ]);

        if (null === $shopIdentity) {
            $this->logger->notice('unknown shop');

            return null;
        }

        $identity = $this->identityService->findOneOrCreate(
            (string) $entry['id'],
            PlentymarketsAdapter::NAME,
            Order::TYPE
        );

        $shippingProfileIdentity = $this->getShippingProfileIdentity($entry);
        if (null === $shippingProfileIdentity) {
            $this->logger->notice('no shipping profile found');

            return null;
        }

        $currencyIdentity = $this->getCurrencyIdentity($entry);
        if (null === $currencyIdentity) {
            $this->logger->notice('no currency found');

            return null;
        }

        $paymentMethodIdentity = $this->getPaymentMethodIdentity($entry);
        if (null === $paymentMethodIdentity) {
            $this->logger->notice('no payment method found');

            return null;
        }

        $paymentStatusIdentity = $this->getPaymentStatusIdentity($entry);
        if (null === $paymentStatusIdentity) {
            $this->logger->notice('no payment status found');

            return null;
        }

        $oderStatusIdentity = $this->getOrderStatusIdentity($entry);
        if (null === $oderStatusIdentity) {
            $this->logger->notice('no order status found');

            return null;
        }

        $entry['customerData'] = $this->getCustomerData($entry);
        if (empty($entry['customerData'])) {
            return null;
        }

        $entry['billingAddressData'] = $this->getBillingAddressData($entry);
        if (empty($entry['billingAddressData'])) {
            return null;
        }

        $entry['shippingAddressData'] = $this->getShippingAddressData($entry);
        if (empty($entry['shippingAddressData'])) {
            return null;
        }

        $order = new Order();
        $order->setIdentifier($identity->getObjectIdentifier());
        $order->setOrderType($entry['typeId'] === 1 ? Order::TYPE_ORDER : Order::TYPE_OFFER);
        $order->setOrderNumber($this->getOrdernumber($entry));
        $order->setOrderTime($this->getOrderTime($entry));
        $order->setCustomer($this->getCustomer($entry));
        $order->setBillingAddress($this->getBillingAddress($entry));
        $order->setShippingAddress($this->getShippingAddress($entry));
        $order->setOrderItems($this->getOrderItems($entry));
        $order->setPayments([]);
        $order->setShopIdentifier($shopIdentity->getObjectIdentifier());
        $order->setCurrencyIdentifier($currencyIdentity->getObjectIdentifier());
        $order->setOrderStatusIdentifier($oderStatusIdentity->getObjectIdentifier());
        $order->setPaymentStatusIdentifier($paymentStatusIdentity->getObjectIdentifier());
        $order->setPaymentMethodIdentifier($paymentMethodIdentity->getObjectIdentifier());
        $order->setShippingProfileIdentifier($shippingProfileIdentity->getObjectIdentifier());
        $order->setComments($this->getComments($entry));
        $order->setPaymentData([]);
        $order->setPackages($this->getPackages($entry));
        $order->setAttributes([]);

        return $order;
    }

    /**
     * @param array $entry
     *
     * @return array
     */
    private function getBillingAddressData(array $entry)
    {
        $billingAddress = array_filter($entry['addressRelations'], function (array $address) {
            return $address['typeId'] === 1;
        });

        if (empty($billingAddress)) {
            return null;
        }

        $billingAddress = array_shift($billingAddress);

        return $this->addressApi->find($billingAddress['addressId']);
    }

    /**
     * @param array $entry
     *
     * @return array
     */
    private function getShippingAddressData(array $entry)
    {
        $shippingAddress = array_filter($entry['addressRelations'], function (array $address) {
            return $address['typeId'] === 2;
        });

        if (empty($shippingAddress)) {
            return null;
        }

        $shippingAddress = array_shift($shippingAddress);

        return $this->addressApi->find($shippingAddress['addressId']);
    }

    /**
     * @param array $entry
     *
     * @return array
     */
    private function getCustomerData(array $entry)
    {
        $relations = array_filter($entry['relations'], function (array $relation) {
            return $relation['referenceType'] === 'contact';
        });

        if (empty($relations)) {
            return null;
        }

        $relation = array_shift($relations);

        return $this->customerApi->find($relation['referenceId']);
    }

    /**
     * @param array $entry
     *
     * @return null|value
     */
    private function getOrdernumber(array $entry)
    {
        $property = array_filter($entry['properties'], function (array $property) {
            return $property['typeId'] === 7;
        });

        if (!empty($property)) {
            $property = array_shift($property);

            return $property['value'];
        }

        return null;
    }

    /**
     * @param array $entry
     *
     * @return null|Identity
     */
    private function getLanguageIdentity(array $entry)
    {
        $property = array_filter($entry['properties'], function (array $property) {
            return $property['typeId'] === 6;
        });

        if (!empty($property)) {
            $property = array_shift($property);

            $identity = $this->identityService->findOneBy([
                'adapterIdentifier' => (string) $property['value'],
                'adapterName' => PlentymarketsAdapter::NAME,
                'objectType' => Language::TYPE,
            ]);

            return $identity;
        }

        return null;
    }

    /**
     * @param array $entry
     *
     * @return Comment[]
     */
    private function getComments(array $entry)
    {
        $comments = $this->commentApi->findBy([
            'referenceType' => 'order',
            'referenceValue' => $entry['id'],
        ]);

        $result = [];
        foreach ($comments as $data) {
            $comment = new Comment();
            $comment->setComment((string) $data['text']);
            $comment->setType($data['text'] ? Comment::TYPE_CUSTOMER : Comment::TYPE_INTERNAL);

            $result[] = $comment;
        }

        return $result;
    }

    /**
     * @param array $entry
     *
     * @return Customer
     */
    private function getCustomer(array $entry)
    {
        $languageIdentity = $this->getLanguageIdentity($entry);

        $cutomerGroupIdentity = $this->getCustomerGroupIdentity($entry['customerData']);
        if (null === $cutomerGroupIdentity) {
            return null;
        }

        $shopIdentity = $this->getShopIdentity($entry['customerData']);
        if (null === $shopIdentity) {
            return null;
        }

        $customer = new Customer();
        $customer->setType(Customer::TYPE_NORMAL);
        $customer->setNumber($entry['customerData']['number']);
        $customer->setEmail($this->getMail($entry));
        $customer->setNewsletter(false);
        $customer->setLanguageIdentifier($languageIdentity->getObjectIdentifier());
        $customer->setCustomerGroupIdentifier($cutomerGroupIdentity->getObjectIdentifier());
        $customer->setSalutation($entry['customerData']['gender'] === 'male' ? Customer::SALUTATION_MR : Customer::SALUTATION_MS);
        $customer->setTitle('');
        $customer->setFirstname($entry['customerData']['firstName']);
        $customer->setLastname($entry['customerData']['lastName']);
        $customer->setBirthday(null);
        $customer->setPhoneNumber($this->getPhoneNumber($entry));
        $customer->setMobilePhoneNumber($this->getMobilePhoneNumber($entry));
        $customer->setShopIdentifier($shopIdentity->getObjectIdentifier());

        return $customer;
    }

    /**
     * @param array $plentyCustomer
     *
     * @return Identity
     */
    private function getShopIdentity(array $plentyCustomer)
    {
        static $webstores = null;

        if (null === $webstores) {
            $webstores = $this->webstoreApi->findAll();
        }

        return $this->identityService->findOneBy([
            'adapterIdentifier' => (string) $webstores[$plentyCustomer['plentyId']]['storeIdentifier'],
            'adapterName' => PlentymarketsAdapter::NAME,
            'objectType' => Shop::TYPE,
        ]);
    }

    /**
     * @param array $plentyCustomer
     *
     * @return Identity
     */
    private function getCustomerGroupIdentity(array $plentyCustomer)
    {
        return $this->identityService->findOneBy([
            'adapterIdentifier' => (string) $plentyCustomer['classId'],
            'adapterName' => PlentymarketsAdapter::NAME,
            'objectType' => CustomerGroup::TYPE,
        ]);
    }

    /**
     * @param array $entry
     *
     * @return null|Identity
     */
    private function getShippingProfileIdentity(array $entry)
    {
        $shippingProfiles = [];
        foreach ($entry['orderItems'] as $item) {
            $shippingProfiles[] = $item['shippingProfileId'];
        }

        if (!empty($shippingProfiles)) {
            $shippingProfile = array_shift($shippingProfiles);

            $identity = $this->identityService->findOneBy([
                'adapterIdentifier' => (string) $shippingProfile,
                'adapterName' => PlentymarketsAdapter::NAME,
                'objectType' => ShippingProfile::TYPE,
            ]);

            return $identity;
        }

        return null;
    }

    /**
     * @param array $entry
     *
     * @return null|Identity
     */
    private function getCurrencyIdentity(array $entry)
    {
        if (empty($entry['amounts'])) {
            return null;
        }

        $amount = array_shift($entry['amounts']);

        return $this->identityService->findOneBy([
            'adapterIdentifier' => (string) $amount['currency'],
            'adapterName' => PlentymarketsAdapter::NAME,
            'objectType' => Currency::TYPE,
        ]);
    }

    /**
     * @param array $entry
     *
     * @return null|Identity
     */
    private function getPaymentMethodIdentity(array $entry)
    {
        $property = array_filter($entry['properties'], function (array $property) {
            return $property['typeId'] === 3;
        });

        if (!empty($property)) {
            $property = array_shift($property);

            $identity = $this->identityService->findOneBy([
                'adapterIdentifier' => (string) $property['value'],
                'adapterName' => PlentymarketsAdapter::NAME,
                'objectType' => PaymentMethod::TYPE,
            ]);

            return $identity;
        }

        return null;
    }

    /**
     * @param array $entry
     *
     * @return null|Identity
     */
    private function getPaymentStatusIdentity(array $entry)
    {
        $property = array_filter($entry['properties'], function (array $property) {
            return $property['typeId'] === 4;
        });

        if (!empty($property)) {
            //$property = array_shift($property);

            $identity = $this->identityService->findOneBy([
                'adapterIdentifier' => 2, //(string) $property['value'],
                'adapterName' => PlentymarketsAdapter::NAME,
                'objectType' => PaymentStatus::TYPE,
            ]);

            return $identity;
        }

        return null;
    }

    /**
     * @param array $entry
     *
     * @return null|Identity
     */
    private function getOrderStatusIdentity(array $entry)
    {
        return $this->identityService->findOneBy([
            'adapterIdentifier' => (string) $entry['statusId'],
            'adapterName' => PlentymarketsAdapter::NAME,
            'objectType' => OrderStatus::TYPE,
        ]);
    }

    /**
     * @param array $entry
     *
     * @return \DateTimeImmutable|null
     */
    private function getOrderTime(array $entry)
    {
        $date = array_filter($entry['dates'], function (array $property) {
            return $property['typeId'] === 2;
        });

        if (!empty($date)) {
            $date = array_shift($date);

            return \DateTimeImmutable::createFromFormat(DATE_W3C, $date['date']);
        }

        return null;
    }

    /**
     * @param array $entry
     *
     * @return Address
     */
    private function getBillingAddress(array $entry)
    {
        if (empty($entry['billingAddressData'])) {
            return null;
        }

        $countryIdentity = $this->identityService->findOneBy([
            'adapterIdentifier' => (string) $entry['billingAddressData']['countryId'],
            'adapterName' => PlentymarketsAdapter::NAME,
            'objectType' => Country::TYPE,
        ]);

        if (null === $countryIdentity) {
            $this->logger->error('country not found');

            return null;
        }

        $street = $entry['billingAddressData']['address1'] . ' ' . $entry['billingAddressData']['address2'] . ' ' . $entry['billingAddressData']['address3'];

        $address = new Address();
        $address->setCompany('');
        $address->setDepartment('');
        $address->setSalutation(1);
        $address->setTitle('');
        $address->setFirstname($entry['billingAddressData']['name2']);
        $address->setLastname($entry['billingAddressData']['name3']);
        $address->setStreet(trim($street));
        $address->setPostalCode($entry['billingAddressData']['postalCode']);
        $address->setCity($entry['billingAddressData']['town']);
        $address->setCountryIdentifier($countryIdentity->getObjectIdentifier());
        $address->setVatId('');
        $address->setPhoneNumber($this->getPhoneNumber($entry));
        $address->setMobilePhoneNumber($this->getMobilePhoneNumber($entry));
        $address->setAttributes([]);

        return $address;
    }

    /**
     * @param array $entry
     *
     * @return null|Address
     */
    private function getShippingAddress(array $entry)
    {
        if (empty($entry['shippingAddressData'])) {
            return null;
        }

        $countryIdentity = $this->identityService->findOneBy([
            'adapterIdentifier' => (string) $entry['shippingAddressData']['countryId'],
            'adapterName' => PlentymarketsAdapter::NAME,
            'objectType' => Country::TYPE,
        ]);

        if (null === $countryIdentity) {
            $this->logger->error('country not found');

            return null;
        }

        $street = $entry['shippingAddressData']['address1'] . ' ' . $entry['shippingAddressData']['address2'] . ' ' . $entry['shippingAddressData']['address3'];

        $address = new Address();
        $address->setCompany('');
        $address->setDepartment('');
        $address->setSalutation(1);
        $address->setTitle('');
        $address->setFirstname($entry['shippingAddressData']['name2']);
        $address->setLastname($entry['shippingAddressData']['name3']);
        $address->setStreet(trim($street));
        $address->setPostalCode($entry['shippingAddressData']['postalCode']);
        $address->setCity($entry['shippingAddressData']['town']);
        $address->setCountryIdentifier($countryIdentity->getObjectIdentifier());
        $address->setVatId('');
        $address->setPhoneNumber($this->getPhoneNumber($entry));
        $address->setMobilePhoneNumber($this->getMobilePhoneNumber($entry));
        $address->setAttributes([]);

        return $address;
    }

    /**
     * @param array $entry
     *
     * @return null|string
     */
    private function getPhoneNumber(array $entry)
    {
        $options = array_filter($entry['customerData']['options'], function (array $option) {
            return $option['typeId'] === 1 && $option['subTypeId'] === 4;
        });

        if (!empty($options)) {
            $option = array_shift($options);

            return $option['value'];
        }

        return null;
    }

    /**
     * @param array $entry
     *
     * @return null|string
     */
    private function getMobilePhoneNumber(array $entry)
    {
        $options = array_filter($entry['customerData']['options'], function (array $option) {
            return $option['typeId'] === 1 && $option['subTypeId'] === 2;
        });

        if (!empty($options)) {
            $option = array_shift($options);

            return $option['value'];
        }

        return null;
    }

    /**
     * @param array $entry
     *
     * @return null|string
     */
    private function getMail(array $entry)
    {
        if (empty($entry['billingAddressData']['options'])) {
            return null;
        }

        $options = array_filter($entry['billingAddressData']['options'], function (array $option) {
            return $option['typeId'] === 5;
        });

        if (empty($options)) {
            return null;
        }

        $option = array_shift($options);

        return $option['value'];
    }

    /**
     * @param array $entry
     *
     * @return Package[]
     */
    private function getPackages(array $entry)
    {
        $numbers = $this->client->request('GET', 'orders/' . $entry['id'] . '/packagenumbers');

        $shoppingDate = array_filter($entry['dates'], function (array $date) {
            return $date['typeId'] === 8;
        });

        if (!empty($shoppingDate)) {
            $shoppingDate = array_shift($shoppingDate);
        }

        $result = [];
        foreach ($numbers as $number) {
            $package = new Package();
            $package->setShippingCode((string) $number);

            if (!empty($shoppingDate)) {
                $package->setShippingTime(\DateTimeImmutable::createFromFormat(DATE_ATOM, $shoppingDate['date']));
            }

            $result[] = $package;
        }

        return $result;
    }

    /**
     * @param array $entry
     *
     * @return OrderItem[]
     */
    private function getOrderItems(array $entry)
    {
        $result = [];

        foreach ($entry['orderItems'] as $item) {
            $number = $this->getNumberFromVariation($item['itemVariationId']);

            $orderItem = new OrderItem();
            $orderItem->setQuantity((float) $item['quantity']);
            $orderItem->setName($item['orderItemName']);
            $orderItem->setNumber($number);
            $orderItem->setPrice($this->getOrderItemPrice($item));
            $orderItem->setVatRateIdentifier();
            $orderItem->setAttributes([]);

            $result[] = $orderItem;
        }

        return $result;
    }

    /**
     * @param array $item
     *
     * @return float
     */
    private function getOrderItemPrice(array $item)
    {
        return 0.0;
    }

    /**
     * @param int $variationId
     *
     * @return string
     */
    private function getNumberFromVariation($variationId)
    {
        static $variations;

        if (!isset($variations[$variationId])) {
            $response = $this->client->request('GET', 'items/variations', ['id' => $variationId]);

            if (empty($response)) {
                $variations[$variationId] = null;

                return $variations[$variationId];
            }

            $variation = array_shift($response);

            $variations[$variationId] = $variation['number'];
        }

        return $variations[$variationId];
    }
}
