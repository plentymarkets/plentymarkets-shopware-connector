<?php

namespace ShopwareAdapter\ResponseParser\Order;

use Assert\Assertion;
use PlentyConnector\Connector\IdentityService\Exception\NotFoundException;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\Currency\Currency;
use PlentyConnector\Connector\TransferObject\Order\Address\Address;
use PlentyConnector\Connector\TransferObject\Order\Comment\Comment;
use PlentyConnector\Connector\TransferObject\Order\Order;
use PlentyConnector\Connector\TransferObject\OrderStatus\OrderStatus;
use PlentyConnector\Connector\TransferObject\PaymentMethod\PaymentMethod;
use PlentyConnector\Connector\TransferObject\PaymentStatus\PaymentStatus;
use PlentyConnector\Connector\TransferObject\ShippingProfile\ShippingProfile;
use PlentyConnector\Connector\TransferObject\Shop\Shop;
use PlentymarketsAdapter\ResponseParser\GetAttributeTrait;
use Shopware\Components\Model\ModelRepository;
use ShopwareAdapter\ResponseParser\Address\AddressResponseParser;
use ShopwareAdapter\ResponseParser\Address\AddressResponseParserInterface;
use ShopwareAdapter\ResponseParser\Customer\CustomerResponseParserInterface;
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
     * OrderResponseParser constructor.
     *
     * @param IdentityServiceInterface $identityService
     * @param OrderItemResponseParserInterface $orderItemResponseParser
     * @param AddressResponseParser|AddressResponseParserInterface $addressResponseParser
     * @param CustomerResponseParserInterface $customerParser
     */
    public function __construct(
        IdentityServiceInterface $identityService,
        OrderItemResponseParserInterface $orderItemResponseParser,
        AddressResponseParserInterface $addressResponseParser,
        CustomerResponseParserInterface $customerParser
    ) {
        $this->identityService = $identityService;
        $this->orderItemResponseParser = $orderItemResponseParser;
        $this->orderAddressParser = $addressResponseParser;
        $this->orderAddressParser = $addressResponseParser;
        $this->customerParser = $customerParser;
    }

    /**
     * {@inheritdoc}
     *
     * @throws NotFoundException
     */
    public function parse(array $entry)
    {

        $orderItems = array_filter(array_map(function ($orderItem) {
            return $this->orderItemResponseParser->parse($orderItem);
        }, $entry['details']));

        /** @var Address $billingAddress */
        $billingAddress = $this->orderAddressParser->parse($entry['billing']);
        /** @var Address $shippingAddress */
        $shippingAddress = $this->orderAddressParser->parse($entry['shipping']);
        $customer = $this->customerParser->parse($entry['customer']);
        $customer->setMobilePhoneNumber($billingAddress->getMobilePhoneNumber());
        $customer->setPhoneNumber($billingAddress->getPhoneNumber());

        $order = Order::fromArray(
            [
                'orderNumber' => $entry['number'],
                'orderItems' => $orderItems,
                'attributes' => $this->getAttributes(['attribute']),
                'billingAddress' => $billingAddress,
                'shippingAddress' => $shippingAddress,
                'comments' => $this->getComments($entry),
                'customer' => $customer,
                'phoneNumber' => $entry['billing']['phone'],
                'orderTime' => $entry['orderTime'],
                'orderType' => Order::TYPE_ORDER
            ]
            + $this->fetchMappedAttributes($entry)
        );

        return $order;
    }

    /**
     * @param $entry
     * @return Comment[]
     */
    private function getComments($entry)
    {
        $comments = [];
        if ($entry['comment']) {//TODO: check type
            $comment = new Comment();
            $comment->setType(Comment::TYPE_INTERNAL);
            $comment->setComment($entry['comment']);
            $comments[] = $comment;
        }
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
     * @param int $entry
     * @return string
     */
    private function getIdentifier($entry, $type)
    {
        Assertion::integer($entry);
        return $this->identityService->findOneOrThrow(
            (string)$entry,
            ShopwareAdapter::NAME,
            $type
        )->getObjectIdentifier();
    }

    /**
     * @param string $entry
     * @return mixed
     */
    private function getCurrencyId($entry)
    {
        /** @var ModelRepository $currencyRepo */
        $currencyRepo = Shopware()->Models()->getRepository(\Shopware\Models\Shop\Currency::class);
        return $currencyRepo->findOneBy(['currency' => $entry])->getId();
    }

    private function fetchMappedAttributes($entry)
    {
        $orderIdentifier = $this->identityService->findOneOrCreate(
            (string)$entry['id'],
            ShopwareAdapter::NAME,
            Order::TYPE
        )->getObjectIdentifier();

        $shopIdentity = $this->getIdentifier($entry['shopId'], Shop::TYPE);
        $orderStatusIdentity = $this->getIdentifier($entry['orderStatusId'], OrderStatus::TYPE);
        $paymentStatusIdentity = $this->getIdentifier($entry['paymentStatusId'], PaymentStatus::TYPE);
        $paymentMethodIdentity = $this->getIdentifier($entry['paymentId'], PaymentMethod::TYPE);
        $shippingProfileIdentity = $this->getIdentifier($entry['dispatchId'], ShippingProfile::TYPE);
        $currencyIdentifier = $this->getIdentifier($this->getCurrencyId($entry['currency']), Currency::TYPE);

        return [
            'identifier' => $orderIdentifier,
            'orderStatusId' => $orderStatusIdentity,
            'paymentStatusId' => $paymentStatusIdentity,
            'paymentMethodId' => $paymentMethodIdentity,
            'shippingProfileId' => $shippingProfileIdentity,
            'currencyIdentifier' => $currencyIdentifier,
            'shopIdentifier' => $shopIdentity,
            'shopId' => $shopIdentity,
        ];
    }


}
