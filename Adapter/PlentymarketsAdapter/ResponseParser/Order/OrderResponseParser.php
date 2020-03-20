<?php

namespace PlentymarketsAdapter\ResponseParser\Order;

use DateTimeImmutable;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ReadApi\Customer\Customer as CustomerApi;
use Psr\Log\LoggerInterface;
use SystemConnector\IdentityService\IdentityServiceInterface;
use SystemConnector\IdentityService\Struct\Identity;
use SystemConnector\TransferObject\Country\Country;
use SystemConnector\TransferObject\Currency\Currency;
use SystemConnector\TransferObject\CustomerGroup\CustomerGroup;
use SystemConnector\TransferObject\Language\Language;
use SystemConnector\TransferObject\Order\Address\Address;
use SystemConnector\TransferObject\Order\Comment\Comment;
use SystemConnector\TransferObject\Order\Customer\Customer;
use SystemConnector\TransferObject\Order\Order;
use SystemConnector\TransferObject\Order\OrderItem\OrderItem;
use SystemConnector\TransferObject\Order\Package\Package;
use SystemConnector\TransferObject\OrderStatus\OrderStatus;
use SystemConnector\TransferObject\PaymentMethod\PaymentMethod;
use SystemConnector\TransferObject\PaymentStatus\PaymentStatus;
use SystemConnector\TransferObject\ShippingProfile\ShippingProfile;
use SystemConnector\TransferObject\Shop\Shop;

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
     * @var CustomerApi
     */
    private $customerApi;

    /**
     * @var ClientInterface
     */
    private $client;

    public function __construct(
        IdentityServiceInterface $identityService,
        LoggerInterface $logger,
        CustomerApi $customerApi,
        ClientInterface $client
    ) {
        $this->identityService = $identityService;
        $this->logger = $logger;
        $this->customerApi = $customerApi;
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(array $entry): array
    {
        $identity = $this->identityService->findOneBy([
            'adapterIdentifier' => (string) $entry['id'],
            'adapterName' => PlentymarketsAdapter::NAME,
            'objectType' => Order::TYPE,
        ]);

        if (!$identity) {
            return [];
        }

        $shopIdentity = $this->identityService->findOneBy([
            'adapterIdentifier' => (string) $entry['plentyId'],
            'adapterName' => PlentymarketsAdapter::NAME,
            'objectType' => Shop::TYPE,
        ]);

        if (null === $shopIdentity) {
            $this->logger->notice('unknown shop');

            return [];
        }

        $isMappedShopIdentity = $this->identityService->isMappedIdentity(
            $shopIdentity->getObjectIdentifier(),
            $shopIdentity->getObjectType(),
            $shopIdentity->getAdapterName()
        );

        if (!$isMappedShopIdentity) {
            return [];
        }

        $orderNumber = $this->getOrdernumber($entry);
        if (null === $orderNumber) {
            return [];
        }

        $shippingProfileIdentity = $this->getShippingProfileIdentity($entry);
        if (null === $shippingProfileIdentity) {
            $this->logger->notice('no shipping profile found', ['entry' => $entry]);

            return [];
        }

        $currencyIdentity = $this->getCurrencyIdentity($entry);
        if (null === $currencyIdentity) {
            $this->logger->notice('no currency found', ['entry' => $entry]);

            return [];
        }

        $paymentMethodIdentity = $this->getPaymentMethodIdentity($entry);
        if (null === $paymentMethodIdentity) {
            $this->logger->notice('no payment method found', ['entry' => $entry]);

            return [];
        }

        $paymentStatusIdentity = $this->getPaymentStatusIdentity($entry);
        if (null === $paymentStatusIdentity) {
            $this->logger->notice('no payment status found', ['entry' => $entry]);

            return [];
        }

        $oderStatusIdentity = $this->getOrderStatusIdentity($entry);
        if (null === $oderStatusIdentity) {
            $this->logger->notice('no order status found', ['entry' => $entry]);

            return [];
        }

        $entry['customerData'] = $this->getCustomerData($entry);
        if (empty($entry['customerData'])) {
            $this->logger->notice('no customer found', ['entry' => $entry]);

            return [];
        }

        $entry['billingAddressData'] = $this->getBillingAddressData($entry);
        if (empty($entry['billingAddressData'])) {
            $this->logger->notice('no billing address found', ['entry' => $entry]);

            return [];
        }

        $entry['shippingAddressData'] = $this->getShippingAddressData($entry);
        if (empty($entry['shippingAddressData'])) {
            $this->logger->notice('no shipping address found', ['entry' => $entry]);

            return [];
        }

        $customer = $this->getCustomer($entry);
        if (null === $customer) {
            $this->logger->notice('no customer found', ['entry' => $entry]);

            return [];
        }

        $order = new Order();
        $order->setIdentifier($identity->getObjectIdentifier());
        $order->setOrderNumber($orderNumber);
        $order->setOrderTime($this->getOrderTime($entry));
        $order->setCustomer($customer);
        $order->setOrderItems($this->getOrderItems($entry));
        $order->setShopIdentifier($shopIdentity->getObjectIdentifier());
        $order->setCurrencyIdentifier($currencyIdentity->getObjectIdentifier());
        $order->setOrderStatusIdentifier($oderStatusIdentity->getObjectIdentifier());
        $order->setPaymentStatusIdentifier($paymentStatusIdentity->getObjectIdentifier());
        $order->setPaymentMethodIdentifier($paymentMethodIdentity->getObjectIdentifier());
        $order->setShippingProfileIdentifier($shippingProfileIdentity->getObjectIdentifier());
        $order->setComments($this->getComments($entry));
        $order->setPackages($this->getPackages($entry));
        $order->setAttributes([]);

        $billingAddress = $this->getBillingAddress($entry);
        if (null !== $billingAddress) {
            $order->setBillingAddress($billingAddress);
        }

        $shippingAddress = $this->getShippingAddress($entry);
        if (null !== $shippingAddress) {
            $order->setShippingAddress($shippingAddress);
        }

        return [$order];
    }

    private function getBillingAddressData(array $entry): array
    {
        $billingAddress = array_filter($entry['addresses'], static function (array $address) {
            return $address['pivot']['typeId'] === 1;
        });

        if (empty($billingAddress)) {
            return [];
        }

        return array_shift($billingAddress);
    }

    private function getShippingAddressData(array $entry): array
    {
        $shippingAddress = array_filter($entry['addresses'], static function (array $address) {
            return $address['pivot']['typeId'] === 2;
        });

        if (empty($shippingAddress)) {
            return [];
        }

        return array_shift($shippingAddress);
    }

    private function getCustomerData(array $entry): array
    {
        $relations = array_filter($entry['relations'], static function (array $relation) {
            return $relation['referenceType'] === 'contact';
        });

        if (empty($relations)) {
            return [];
        }

        $relation = array_shift($relations);

        return $this->customerApi->find($relation['referenceId']);
    }

    /**
     * @return null|string
     */
    private function getOrdernumber(array $entry)
    {
        $property = array_filter($entry['properties'], static function (array $property) {
            return $property['typeId'] === 7;
        });

        if (!empty($property)) {
            $property = array_shift($property);

            return $property['value'];
        }

        return null;
    }

    /**
     * @return null|Identity
     */
    private function getLanguageIdentity(array $entry)
    {
        $property = array_filter($entry['properties'], static function (array $property) {
            return $property['typeId'] === 6;
        });

        if (!empty($property)) {
            $property = array_shift($property);

            return $this->identityService->findOneBy([
                'adapterIdentifier' => (string) $property['value'],
                'adapterName' => PlentymarketsAdapter::NAME,
                'objectType' => Language::TYPE,
            ]);
        }

        return null;
    }

    /**
     * @return Comment[]
     */
    private function getComments(array $entry): array
    {
        $result = [];
        foreach ($entry['comments'] as $data) {
            $comment = new Comment();
            $comment->setComment((string) $data['text']);
            $comment->setType($data['text'] ? Comment::TYPE_CUSTOMER : Comment::TYPE_INTERNAL);

            $result[] = $comment;
        }

        return $result;
    }

    /**
     * @return null|Customer
     */
    private function getCustomer(array $entry)
    {
        $languageIdentity = $this->getLanguageIdentity($entry);

        if (null === $languageIdentity) {
            $this->logger->info('language of customer not found', [
                'entry' => $entry,
            ]);

            return null;
        }

        $customerGroupIdentity = $this->getCustomerGroupIdentity($entry['customerData']);

        if (null === $customerGroupIdentity) {
            $this->logger->info('customer group not found', [
                'entry' => $entry,
            ]);

            return null;
        }

        $shopIdentity = $this->getShopIdentity($entry['customerData']);

        if (null === $shopIdentity) {
            $this->logger->info('customer shop not found', [
                'entry' => $entry,
            ]);

            return null;
        }

        if (null === $entry['customerData']['gender']) {
            $this->logger->info('customer gender not defined', [
                'customerNumber' => $entry['customerData']['number'],
            ]);

            return null;
        }

        $customer = new Customer();
        $customer->setType(Customer::TYPE_NORMAL);
        $customer->setNumber($entry['customerData']['number']);
        $customer->setEmail($this->getMail($entry));
        $customer->setLanguageIdentifier($languageIdentity->getObjectIdentifier());
        $customer->setCustomerGroupIdentifier($customerGroupIdentity->getObjectIdentifier());
        $customer->setGender($entry['customerData']['gender']);
        $customer->setFirstname($entry['customerData']['firstName']);
        $customer->setLastname($entry['customerData']['lastName']);
        $customer->setPhoneNumber($this->getPhoneNumber($entry));
        $customer->setMobilePhoneNumber($this->getMobilePhoneNumber($entry));
        $customer->setShopIdentifier($shopIdentity->getObjectIdentifier());

        return $customer;
    }

    private function getShopIdentity(array $plentyCustomer): Identity
    {
        return $this->identityService->findOneBy([
            'adapterIdentifier' => (string) $plentyCustomer['plentyId'],
            'adapterName' => PlentymarketsAdapter::NAME,
            'objectType' => Shop::TYPE,
        ]);
    }

    /**
     * @return null|Identity
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
     * @return null|Identity
     */
    private function getShippingProfileIdentity(array $entry)
    {
        foreach ($entry['properties'] as $orderProperty) {
            if ($orderProperty['typeId'] !== 2) {
                continue;
            }

            if (!empty($orderProperty['value'])) {
                return $this->identityService->findOneBy([
                    'adapterIdentifier' => (string) $orderProperty['value'],
                    'adapterName' => PlentymarketsAdapter::NAME,
                    'objectType' => ShippingProfile::TYPE,
                ]);
            }
        }

        return null;
    }

    /**
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
     * @return null|Identity
     */
    private function getPaymentMethodIdentity(array $entry)
    {
        $property = array_filter($entry['properties'], static function (array $property) {
            return $property['typeId'] === 3;
        });

        if (!empty($property)) {
            $property = array_shift($property);

            return $this->identityService->findOneBy([
                'adapterIdentifier' => (string) $property['value'],
                'adapterName' => PlentymarketsAdapter::NAME,
                'objectType' => PaymentMethod::TYPE,
            ]);
        }

        return null;
    }

    /**
     * @return null|Identity
     */
    private function getPaymentStatusIdentity(array $entry)
    {
        $property = array_filter($entry['properties'], static function (array $property) {
            return $property['typeId'] === 4;
        });

        if (!empty($property)) {
            //$property = array_shift($property);

            return $this->identityService->findOneBy([
                'adapterIdentifier' => (string) $entry['statusId'],
                'adapterName' => PlentymarketsAdapter::NAME,
                'objectType' => PaymentStatus::TYPE,
            ]);
        }

        return null;
    }

    /**
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

    private function getOrderTime(array $entry): DateTimeImmutable
    {
        $date = array_filter($entry['dates'], static function (array $property) {
            return $property['typeId'] === 2;
        });

        if (!empty($date)) {
            $date = array_shift($date);

            return DateTimeImmutable::createFromFormat(DATE_W3C, $date['date']);
        }

        return new DateTimeImmutable();
    }

    /**
     * @return null|Address
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
        $address->setGender(Customer::GENDER_MALE);
        $address->setFirstname($entry['billingAddressData']['name2']);
        $address->setLastname($entry['billingAddressData']['name3']);
        $address->setStreet(trim($street));
        $address->setPostalCode($entry['billingAddressData']['postalCode']);
        $address->setCity($entry['billingAddressData']['town']);
        $address->setCountryIdentifier($countryIdentity->getObjectIdentifier());
        $address->setPhoneNumber($this->getPhoneNumber($entry));
        $address->setMobilePhoneNumber($this->getMobilePhoneNumber($entry));

        return $address;
    }

    /**
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
        $address->setGender(Customer::GENDER_MALE);
        $address->setFirstname($entry['shippingAddressData']['name2']);
        $address->setLastname($entry['shippingAddressData']['name3']);
        $address->setStreet(trim($street));
        $address->setPostalCode($entry['shippingAddressData']['postalCode']);
        $address->setCity($entry['shippingAddressData']['town']);
        $address->setCountryIdentifier($countryIdentity->getObjectIdentifier());
        $address->setPhoneNumber($this->getPhoneNumber($entry));
        $address->setMobilePhoneNumber($this->getMobilePhoneNumber($entry));

        return $address;
    }

    /**
     * @return null|string
     */
    private function getPhoneNumber(array $entry)
    {
        $options = array_filter($entry['customerData']['options'], static function (array $option) {
            return $option['typeId'] === 1 && $option['subTypeId'] === 4;
        });

        if (!empty($options)) {
            $option = array_shift($options);

            return $option['value'];
        }

        return null;
    }

    /**
     * @return null|string
     */
    private function getMobilePhoneNumber(array $entry)
    {
        $options = array_filter($entry['customerData']['options'], static function (array $option) {
            return $option['typeId'] === 1 && $option['subTypeId'] === 2;
        });

        if (!empty($options)) {
            $option = array_shift($options);

            return $option['value'];
        }

        return null;
    }

    /**
     * @return null|string
     */
    private function getMail(array $entry)
    {
        if (empty($entry['billingAddressData']['options'])) {
            return null;
        }

        $options = array_filter($entry['billingAddressData']['options'], static function (array $option) {
            return $option['typeId'] === 5;
        });

        if (empty($options)) {
            return null;
        }

        $option = array_shift($options);

        return $option['value'];
    }

    /**
     * @return Package[]
     */
    private function getPackages(array $entry): array
    {
        $numbers = $this->client->request('GET', 'orders/' . $entry['id'] . '/packagenumbers');

        $shippingDate = array_filter($entry['dates'], static function (array $date) {
            return $date['typeId'] === 8;
        });

        if (!empty($shippingDate)) {
            $shippingDate = array_shift($shippingDate);

            $shippingDate = DateTimeImmutable::createFromFormat(
                DATE_ATOM,
                $shippingDate['date']
            );
        } else {
            $now = new \DateTime();
            $shippingDate = DateTimeImmutable::createFromFormat(
                DATE_ATOM,
                $now->format(DATE_ATOM)
            );
        }

        $result = [];
        foreach ($numbers as $number) {
            $package = new Package();
            $package->setShippingCode((string) $number);
            $package->setShippingProvider();
            $package->setShippingTime($shippingDate);

            $result[] = $package;
        }

        return $result;
    }

    /**
     * @return OrderItem[]
     */
    private function getOrderItems(array $entry): array
    {
        $result = [];

        foreach ($entry['orderItems'] as $item) {
            $number = $this->getNumberFromVariation($item['itemVariationId']);

            if (empty($number)) {
                $this->logger->info('product not found', [
                    'itemVariationId' => $item['itemVariationId'],
                ]);
                continue;
            }

            $orderItem = new OrderItem();
            $orderItem->setQuantity((float) $item['quantity']);
            $orderItem->setName($item['orderItemName']);
            $orderItem->setNumber($number);
            $orderItem->setPrice($this->getOrderItemPrice($item));
            $orderItem->setAttributes([]);

            $result[] = $orderItem;
        }

        return $result;
    }

    private function getOrderItemPrice(array $item): float
    {
        $price = 0.0;

        foreach ($item['amounts'] as $amount) {
            $price += $amount['priceOriginalGross'];
        }

        return $price;
    }

    private function getNumberFromVariation($variationId): ?string
    {
        static $variations;

        if (!isset($variations[$variationId])) {
            $response = $this->client->request('GET', 'items/variations', ['id' => $variationId]);

            if (empty($response)) {
                return null;
            }

            $variation = array_shift($response);

            $variations[$variationId] = $variation['number'];
        }

        return $variations[$variationId];
    }
}
