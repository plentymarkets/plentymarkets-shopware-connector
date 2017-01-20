<?php

namespace ShopwareAdapter\ResponseParser\Order;

use PlentyConnector\Connector\IdentityService\Exception\NotFoundException;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\Order\Order;
use PlentyConnector\Connector\TransferObject\OrderStatus\OrderStatus;
use PlentyConnector\Connector\TransferObject\PaymentMethod\PaymentMethod;
use PlentyConnector\Connector\TransferObject\PaymentStatus\PaymentStatus;
use PlentyConnector\Connector\TransferObject\ShippingProfile\ShippingProfile;
use PlentyConnector\Connector\TransferObject\Shop\Shop;
use ShopwareAdapter\ResponseParser\OrderItem\OrderItemResponseParserInterface;
use ShopwareAdapter\ShopwareAdapter;

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
     * @var OrderItemResponseParserInterface
     */
    private $orderItemResponseParser;

    /**
     * OrderResponseParser constructor.
     *
     * @param IdentityServiceInterface $identityService
     * @param OrderItemResponseParserInterface $orderItemResponseParser
     */
    public function __construct(
        IdentityServiceInterface $identityService,
        OrderItemResponseParserInterface $orderItemResponseParser
    ) {
        $this->identityService = $identityService;
        $this->orderItemResponseParser = $orderItemResponseParser;
    }

    /**
     * {@inheritdoc}
     *
     * @throws NotFoundException
     */
    public function parse(array $entry)
    {
        $identity = $this->identityService->findOneOrCreate(
            (string)$entry['id'],
            ShopwareAdapter::NAME,
            Order::TYPE
        );

        $orderItems = array_filter(array_map(function ($orderItem) {
            return $this->orderItemResponseParser->parse($orderItem);
        }, $entry['details']));

        $shopIdentity = $this->identityService->findOneOrThrow(
            (string)$entry['shopId'],
            ShopwareAdapter::NAME,
            Shop::TYPE
        );
        $orderStatusIdentity = $this->identityService->findOneOrThrow(
            (string)$entry['orderStatusId'],
            ShopwareAdapter::NAME,
            OrderStatus::TYPE
        );
        $paymentStatusIdentity = $this->identityService->findOneOrThrow(
            (string)$entry['paymentStatusId'],
            ShopwareAdapter::NAME,
            PaymentStatus::TYPE
        );
        $paymentMethodIdentity = $this->identityService->findOneOrThrow(
            (string)$entry['paymentId'],
            ShopwareAdapter::NAME,
            PaymentMethod::TYPE
        );
        $shippingProfileIdentity = $this->identityService->findOneOrThrow(
            (string)$entry['dispatchId'],
            ShopwareAdapter::NAME,
            ShippingProfile::TYPE
        );

        $order = Order::fromArray([
            'identifier' => $identity->getObjectIdentifier(),
            'orderNumber' => $entry['number'],
            'orderItems' => $orderItems,
            'orderStatusId' => $orderStatusIdentity->getObjectIdentifier(),
            'paymentStatusId' => $paymentStatusIdentity->getObjectIdentifier(),
            'paymentMethodId' => $paymentMethodIdentity->getObjectIdentifier(),
            'shippingProfileId' => $shippingProfileIdentity->getObjectIdentifier(),
            'shopId' => $shopIdentity->getObjectIdentifier(),
        ]);

        return $order;
    }
}
