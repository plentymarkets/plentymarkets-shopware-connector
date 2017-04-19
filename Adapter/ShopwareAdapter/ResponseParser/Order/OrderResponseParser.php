<?php

namespace ShopwareAdapter\ResponseParser\Order;

use Assert\Assertion;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;
use PlentyConnector\Connector\IdentityService\Exception\NotFoundException;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\Currency\Currency;
use PlentyConnector\Connector\TransferObject\Order\Comment\Comment;
use PlentyConnector\Connector\TransferObject\Order\Order;
use PlentyConnector\Connector\TransferObject\Order\OrderItem\OrderItem;
use PlentyConnector\Connector\TransferObject\Order\Payment\Payment;
use PlentyConnector\Connector\TransferObject\Order\PaymentData\PaymentDataInterface;
use PlentyConnector\Connector\TransferObject\Order\PaymentData\PayPalPlusInvoicePaymentData;
use PlentyConnector\Connector\TransferObject\Customer\BankAccount\BankAccount;
use PlentyConnector\Connector\TransferObject\OrderStatus\OrderStatus;
use PlentyConnector\Connector\TransferObject\PaymentMethod\PaymentMethod;
use PlentyConnector\Connector\TransferObject\PaymentStatus\PaymentStatus;
use PlentyConnector\Connector\TransferObject\ShippingProfile\ShippingProfile;
use PlentyConnector\Connector\TransferObject\Shop\Shop;
use PlentyConnector\Connector\TransferObject\VatRate\VatRate;
use PlentymarketsAdapter\ResponseParser\GetAttributeTrait;
use Psr\Log\LoggerInterface;
use Shopware\Components\Model\ModelRepository;
use Shopware\Models\Order\Status;
use Shopware\Models\Tax\Repository;
use Shopware\Models\Tax\Tax;
use ShopwareAdapter\ResponseParser\Address\AddressResponseParserInterface;
use ShopwareAdapter\ResponseParser\Customer\CustomerResponseParserInterface;
use ShopwareAdapter\ResponseParser\OrderItem\Exception\UnsupportedVatRateException;
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
     * @param LoggerInterface                  $logger
     */
    public function __construct(
        IdentityServiceInterface $identityService,
        EntityManagerInterface $entityManager,
        OrderItemResponseParserInterface $orderItemResponseParser,
        AddressResponseParserInterface $orderAddressParser,
        CustomerResponseParserInterface $customerParser,
        LoggerInterface $logger
    ) {
        $this->identityService = $identityService;
        $this->entityManager = $entityManager;
        $this->orderItemResponseParser = $orderItemResponseParser;
        $this->orderAddressParser = $orderAddressParser;
        $this->customerParser = $customerParser;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(array $entry)
    {
        $taxFree = ($entry['net'] || $entry['taxFree']);

        $entry['details'] = $this->prepareOrderItems($entry['details']);

        try {
            $orderItems = array_filter(array_map(function ($orderItem) use ($taxFree) {
                return $this->orderItemResponseParser->parse($orderItem, $taxFree);
            }, $entry['details']));
        } catch (UnsupportedVatRateException $exception) {
            $this->logger->notice('unsupported vat rate - order: ' . $entry['number']);

            return null;
        }

        $shippingCosts = $this->getShippingCosts($entry, $taxFree);

        if (null !== $shippingCosts) {
            $orderItems[] = $shippingCosts;
        }

        $billingAddress = $this->orderAddressParser->parse($entry['billing']);
        $shippingAddress = $this->orderAddressParser->parse($entry['shipping']);

        $customer = $this->customerParser->parse($entry['customer']);
        $customer->setMobilePhoneNumber($billingAddress->getMobilePhoneNumber());
        $customer->setPhoneNumber($billingAddress->getPhoneNumber());

        $orderIdentifier = $this->identityService->findOneOrCreate(
            (string) $entry['id'],
            ShopwareAdapter::NAME,
            Order::TYPE
        )->getObjectIdentifier();

        $shopIdentity = $this->getIdentifier($entry['shopId'], Shop::TYPE);
        $orderStatusIdentifier = $this->getIdentifier($entry['orderStatusId'], OrderStatus::TYPE);
        $paymentStatusIdentifier = $this->getIdentifier($entry['paymentStatusId'], PaymentStatus::TYPE);
        $paymentMethodIdentifier = $this->getIdentifier($entry['paymentId'], PaymentMethod::TYPE);

        $shippingProfileIdentity = $this->identityService->findOneBy([
            'adapterIdentifier' => (string) $entry['dispatchId'],
            'adapterName' => ShopwareAdapter::NAME,
            'objectType' => ShippingProfile::TYPE,
        ]);

        if (null === $shippingProfileIdentity) {
            $this->logger->notice('no shipping profile was selected for order: ' . $entry['number']);

            return null;
        }

        $currencyIdentifier = $this->getIdentifier($this->getCurrencyId($entry['currency']), Currency::TYPE);


        $order = Order::fromArray([
            'orderNumber' => $entry['number'],
            'orderItems' => $orderItems,
            'attributes' => $this->getAttributes($entry['attribute']),
            'billingAddress' => $billingAddress,
            'shippingAddress' => $shippingAddress,
            'comments' => $this->getComments($entry),
            'customer' => $customer,
            'orderTime' => \DateTimeImmutable::createFromMutable($entry['orderTime']),
            'orderType' => Order::TYPE_ORDER,
            'identifier' => $orderIdentifier,
            'orderStatusIdentifier' => $orderStatusIdentifier,
            'paymentStatusIdentifier' => $paymentStatusIdentifier,
            'paymentMethodIdentifier' => $paymentMethodIdentifier,
            'shippingProfileIdentifier' => $shippingProfileIdentity->getObjectIdentifier(),
            'currencyIdentifier' => $currencyIdentifier,
            'shopIdentifier' => $shopIdentity,
        ]);

        return [$order];
    }

    /**
     * @param array $orderItems
     *
     * @throws \Exception
     *
     * @return array
     */
    private function prepareOrderItems(array $orderItems)
    {
        foreach ($orderItems as $key => $orderItem) {
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
                    throw new \Exception('no matching tax rate found - ' . $orderItem['taxRate']);
                }

                $orderItems[$key]['taxId'] = $taxModel->getId();
            }
        }

        return $orderItems;
    }

    /**
     * @param $entry
     *
     * @return Comment[]
     */
    private function getComments($entry)
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
    private function getIdentifier($entry, $type)
    {
        Assertion::integerish($entry);

        return $this->identityService->findOneOrThrow(
            (string) $entry,
            ShopwareAdapter::NAME,
            $type
        )->getObjectIdentifier();
    }

    /**
     * @param string $currency
     *
     * @return int
     */
    private function getCurrencyId($currency)
    {
        /**
         * @var ModelRepository $currencyRepo
         */
        $currencyRepo = Shopware()->Models()->getRepository(\Shopware\Models\Shop\Currency::class);

        return $currencyRepo->findOneBy(['currency' => $currency])->getId();
    }

    /**
     * @param array $entry
     *
     * @throws NotFoundException
     *
     * @return string
     */
    private function getShippingCostsVatRateIdentifier(array $entry)
    {
        if (!isset($entry['dispatch']['taxCalculation'])) {
            return null;
        }

        if ($entry['dispatch']['taxCalculation'] > 0) {
            $identity = $this->identityService->findOneBy([
                'adapterIdentifier' => (string) $entry['dispatch']['taxCalculation'],
                'adapterName' => ShopwareAdapter::NAME,
                'objectType' => VatRate::TYPE,
            ]);

            if (null === $identity) {
                throw new NotFoundException('tax rate of shipping costs not found - ' . $entry['dispatch']['taxCalculation']);
            }

            return $identity->getObjectIdentifier();
        }

        $maxTaxRate = 0;
        $maxTaxRateIdentifier = 0;

        foreach ($entry['details'] as $orderItem) {
            if (empty($orderItem['taxId'])) {
                continue;
            }

            if ($orderItem['taxRate'] < $maxTaxRate) {
                continue;
            }

            $maxTaxRate = $orderItem['taxRate'];
            $maxTaxRateIdentifier = $orderItem['taxId'];
        }

        $identity = $this->identityService->findOneBy([
            'adapterIdentifier' => (string) $maxTaxRateIdentifier,
            'adapterName' => ShopwareAdapter::NAME,
            'objectType' => VatRate::TYPE,
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
     * @return null|OrderItem
     */
    private function getShippingCosts(array $entry, $taxFree = false)
    {
        if ($taxFree) {
            $shippingCosts = (float) $entry['invoiceShippingNet'];
            $vatRateIdentifier = null;
        } else {
            $shippingCosts = (float) $entry['invoiceShipping'];
            $vatRateIdentifier = $this->getShippingCostsVatRateIdentifier($entry);
        }

        if (empty($shippingCosts)) {
            return null;
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

    /**
     * @param array $entry
     *
     * @return null|BankAccount
     */
    private function getSepaPaymentData(array $entry)
    {
        foreach ($entry['paymentInstances'] as $paymentInstance) {
            if (empty($paymentInstance['accountHolder'])) {
                continue;
            }

            if (empty($paymentInstance['iban'])) {
                continue;
            }

            if (empty($paymentInstance['bic'])) {
                continue;
            }

            $sepaPaymentData = new BankAccount();
            $sepaPaymentData->setAccountOwner($paymentInstance['accountHolder']);
            $sepaPaymentData->setIban($paymentInstance['iban']);
            $sepaPaymentData->setIban($paymentInstance['bic']);

            return $sepaPaymentData;
        }

        return null;
    }

    /**
     * @param array $entry
     *
     * @return null|PayPalPlusInvoicePaymentData
     */
    private function getPayPalPlusInvoicePaymentData(array $entry)
    {
        $connection = $this->entityManager->getConnection();

        try {
            $query = 'SELECT * FROM s_payment_paypal_plus_payment_instruction WHERE ordernumber = ?';
            $paypalData = $connection->fetchAssoc($query, [$entry['number']]);
        } catch (DBALException $exception) {
            return null;
        }

        if (empty($paypalData)) {
            return null;
        }

        $paymentData = new PayPalPlusInvoicePaymentData();
        $paymentData->setReferenceNumber($paypalData['reference_number']);
        $paymentData->setInstructionType($paypalData['instruction_type']);
        $paymentData->setBankName($paypalData['bank_name']);
        $paymentData->setAccountHolderName($paypalData['account_holder_name']);
        $paymentData->setInternationalBankAccountNumber($paypalData['international_bank_account_number']);
        $paymentData->setBankIdentifierCode($paypalData['bank_identifier_code']);
        $paymentData->setAmountValue((float) $paypalData['amount_value']);
        $paymentData->setAmountCurrency($paypalData['amount_currency']);

        $timezone = new \DateTimeZone('Europe/Berlin');
        $dueDate = \DateTimeImmutable::createFromFormat('Y-m-d\ H:i:s', $paypalData['payment_due_date'], $timezone);
        $paymentData->setPaymentDueDate($dueDate);

        return $paymentData;
    }

    /**
     * @param array $entry
     *
     * @return PaymentDataInterface[]
     */
    private function getPaymentData(array $entry)
    {
        $paymentData = [];

        $paymentData[] = $this->getSepaPaymentData($entry);
        $paymentData[] = $this->getPayPalPlusInvoicePaymentData($entry);

        return array_filter($paymentData);
    }
}
