<?php

namespace PlentyConnector\tests\Integration\Order;

use PHPUnit\Framework\TestCase;
use PlentyConnector\Connector\ConfigService\ConfigService;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\ServiceBus\Command\Order\HandleOrderCommand;
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
use PlentyConnector\Connector\TransferObject\PaymentMethod\PaymentMethod;
use PlentyConnector\Connector\TransferObject\ShippingProfile\ShippingProfile;
use PlentyConnector\Connector\TransferObject\Shop\Shop;
use PlentyConnector\Connector\TransferObject\VatRate\VatRate;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ServiceBus\CommandHandler\Order\HandleOrderCommandHandler;
use Ramsey\Uuid\Uuid;

/**
 * Class HandleOrderCommandHandlerTest
 */
class HandleOrderCommandHandlerTest extends TestCase
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    public function setUp()
    {
        $this->client = Shopware()->Container()->get('plentmarkets_adapter.client');
        $this->identityService = Shopware()->Container()->get('plenty_connector.identity_service');

        /**
         * @var ConfigService $config
         */
        $config = Shopware()->Container()->get('plenty_connector.config');

        $config->set('rest_url', 'http://arvatis-beta.plentymarkets-cloud01.com');
        $config->set('rest_username', 'py101');
        $config->set('rest_password', '48TRtL73H2');
    }

    public function test_export_order()
    {
        if (getenv('TRAVIS') === 'true') {
            $this->markTestSkipped('This test should not run if on Travis.');
        }

        /**
         * @var HandleOrderCommandHandler $handler
         */
        $handler = Shopware()->Container()->get('plentymarkets_adapter.command_handler.handle_order');

        $command = new HandleOrderCommand(PlentymarketsAdapter::NAME, $this->createOrderTransferObject());

        $this->assertTrue($handler->handle($command));
    }

    /**
     * @return string
     */
    private function getLanguageIdentifier()
    {
        $languageIdentity = $this->identityService->findOneBy([
            'adapterIdentifier' => 'de',
            'adapterName' => PlentymarketsAdapter::NAME,
            'objectType' => Language::TYPE,
        ]);

        return $languageIdentity->getObjectIdentifier();
    }

    /**
     * @return string
     */
    private function getCustomerGroupIdentifier()
    {
        $languageIdentity = $this->identityService->findOneBy([
            'adapterIdentifier' => '1',
            'adapterName' => PlentymarketsAdapter::NAME,
            'objectType' => CustomerGroup::TYPE,
        ]);

        return $languageIdentity->getObjectIdentifier();
    }

    /**
     * @return Customer
     */
    private function getCustomer()
    {
        $customer = new Customer();
        $customer->setType(Customer::TYPE_NORMAL);
        $customer->setNumber('2002');
        $customer->setEmail('maxime@muster.com');
        $customer->setLanguageIdentifier($this->getLanguageIdentifier());
        $customer->setCustomerGroupIdentifier($this->getCustomerGroupIdentifier());
        $customer->setCompany('Company');
        $customer->setNewsletter(false);
        $customer->setDepartment('Department 2');
        $customer->setSalutation('Salutation 2');
        $customer->setTitle('Title 2');
        $customer->setFirstname('Firstname 2');
        $customer->setLastname('Lastname 2');
        $customer->setPhoneNumber('07251/61682');
        $customer->setMobilePhoneNumber('017212 34567');

        return $customer;
    }

    /**
     * @return Address
     */
    private function getAddress()
    {
        static $country;

        if (null === $country) {
            $countries = $this->client->request('GET', 'orders/shipping/countries');

            $country = array_shift($countries);
        }

        $countryIdentity = $this->identityService->findOneOrCreate(
            (string) $country['id'],
            PlentymarketsAdapter::NAME,
            Country::TYPE
        );

        $address = new Address();
        $address->setCompany('Company');
        $address->setDepartment('Department');
        $address->setSalutation('Salutation');
        $address->setTitle('Title');
        $address->setFirstname('Firstname');
        $address->setLastname('Lastname');
        $address->setStreet('Street 2');
        $address->setZipcode('12345');
        $address->setCity('City');
        $address->setCountryIdentifier($countryIdentity->getObjectIdentifier());
        $address->setVatId('');

        return $address;
    }

    /**
     * @return string
     */
    private function getVatRateIdentifier()
    {
        static $vatRate;

        if (null === $vatRate) {
            $vatRates = $this->client->request('GET', 'vat');

            $vatRate = array_shift($vatRates);
        }

        $countryIdentity = $this->identityService->findOneOrCreate(
            (string) $vatRate['id'],
            PlentymarketsAdapter::NAME,
            VatRate::TYPE
        );

        return $countryIdentity->getObjectIdentifier();
    }

    /**
     * @return OrderItem[]
     */
    private function getOrderItems()
    {
        $orderItem = new OrderItem();
        $orderItem->setType(OrderItem::TYPE_PRODUCT);
        $orderItem->setName('test');
        $orderItem->setNumber('105');
        $orderItem->setQuantity(1);
        $orderItem->setPrice(20.0);
        $orderItem->setVatRateIdentifier($this->getVatRateIdentifier());

        $discount = new OrderItem();
        $discount->setType(OrderItem::TYPE_DISCOUNT);
        $discount->setName('Rabatt');
        $discount->setQuantity(1);
        $discount->setPrice(-10.0);
        $discount->setVatRateIdentifier($this->getVatRateIdentifier());

        $shipping = new OrderItem();
        $shipping->setType(OrderItem::TYPE_SHIPPING_COSTS);
        $shipping->setName('Versandkosten');
        $shipping->setQuantity(1);
        $shipping->setPrice(4.0);
        $shipping->setVatRateIdentifier($this->getVatRateIdentifier());

        return [$orderItem, $discount, $shipping];
    }

    /**
     * @return string
     */
    private function getPaymentMethodIdentifier()
    {
        static $paymentMethod;

        if (null === $paymentMethod) {
            $paymentMethods = $this->client->request('GET', 'payments/methods');

            $paymentMethod = array_shift($paymentMethods);
        }

        $paymentMethodIdentity = $this->identityService->findOneOrCreate(
            (string) $paymentMethod['id'],
            PlentymarketsAdapter::NAME,
            PaymentMethod::TYPE
        );

        return $paymentMethodIdentity->getObjectIdentifier();
    }

    /**
     * @return Payment[]
     */
    private function getPayments()
    {
        $currencyIdentity = $this->identityService->findOneOrCreate(
            'EUR',
            PlentymarketsAdapter::NAME,
            Currency::TYPE
        );

        $payment = new Payment();
        $payment->setAmount(200);
        $payment->setCurrencyIdentifier($currencyIdentity->getObjectIdentifier());
        $payment->setPaymentMethodIdentifier($this->getPaymentMethodIdentifier());
        $payment->setTransactionReference('TransactionReference');

        return [$payment];
    }

    /**
     * @return string
     */
    private function getShopIdentifier()
    {
        static $shop;

        if (null === $shop) {
            $shops = $this->client->request('GET', 'webstores');

            $shop = array_shift($shops);
        }

        $shopIdentity = $this->identityService->findOneOrCreate(
            (string) $shop['storeIdentifier'],
            PlentymarketsAdapter::NAME,
            Shop::TYPE
        );

        return $shopIdentity->getObjectIdentifier();
    }

    /**
     * @return array
     */
    private function getComments()
    {
        $internalComment = new Comment();
        $internalComment->setComment('InternalComment');
        $internalComment->setType(Comment::TYPE_INTERNAL);

        $customerComment = new Comment();
        $customerComment->setComment('CustomerComment');
        $customerComment->setType(Comment::TYPE_CUSTOMER);

        return [$customerComment, $internalComment];
    }

    /**
     * @return string
     */
    private function getShippingProfileIdentifier()
    {
        static $shippingProfile;

        if (null === $shippingProfile) {
            $shippingProfiles = $this->client->request('GET', 'orders/shipping/presets');

            $shippingProfile = array_shift($shippingProfiles);
        }

        $identity = $this->identityService->findOneOrCreate(
            (string) $shippingProfile['id'],
            PlentymarketsAdapter::NAME,
            ShippingProfile::TYPE
        );

        return $identity->getObjectIdentifier();
    }

    /**
     * @return string
     */
    private function getCurrencyIdentifier()
    {
        $identity = $this->identityService->findOneOrCreate(
            'EUR',
            PlentymarketsAdapter::NAME,
            Currency::TYPE
        );

        return $identity->getObjectIdentifier();
    }

    /**
     * @return Order
     */
    private function createOrderTransferObject()
    {
        $order = new Order();
        $order->setIdentifier(Uuid::uuid4()->toString());
        $order->setOrderType(Order::TYPE_ORDER);
        $order->setOrderNumber('2000');
        $order->setOrderTime(new \DateTimeImmutable());
        $order->setCustomer($this->getCustomer());
        $order->setBillingAddress($this->getAddress());
        $order->setShippingAddress($this->getAddress());
        $order->setOrderItems($this->getOrderItems());
        $order->setPayments($this->getPayments());
        $order->setShopIdentifier($this->getShopIdentifier());
        $order->setCurrencyIdentifier($this->getCurrencyIdentifier());
        $order->setOrderStatusIdentifier(null);
        $order->setPaymentStatusIdentifier(null);
        $order->setPaymentMethodIdentifier($this->getPaymentMethodIdentifier());
        $order->setShippingProfileIdentifier($this->getShippingProfileIdentifier());
        $order->setComments($this->getComments());

        return $order;
    }
}
