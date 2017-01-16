<?php

namespace ShopwareAdapter\ResponseParser\Order;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\Order\Order;
use PlentyConnector\Connector\TransferObject\OrderStatus\OrderStatus;
use PlentyConnector\Connector\TransferObject\PaymentMethod\PaymentMethod;
use PlentyConnector\Connector\TransferObject\PaymentStatus\PaymentStatus;
use PlentyConnector\Connector\TransferObject\ShippingProfile\ShippingProfile;
use PlentyConnector\Connector\TransferObject\Shop\Shop;
use ShopwareAdapter\ResponseParser\ResponseParserInterface;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class OrderResponseParser
 */
class OrderResponseParser implements ResponseParserInterface
{
    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * @var ResponseParserInterface
     */
    private $orderItemResponseParser;

    /**
     * ResponseParser constructor.
     *
     * @param IdentityServiceInterface $identityService
     * @param ResponseParserInterface $orderItemResponseParser
     */
    public function __construct(IdentityServiceInterface $identityService, ResponseParserInterface $orderItemResponseParser)
    {
        $this->identityService = $identityService;
        $this->orderItemResponseParser = $orderItemResponseParser;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(array $entry)
    {

        $identity = $this->identityService->findOneOrCreate(
            (string)$entry['id'],
            ShopwareAdapter::getName(),
            Order::getType()
        );

        $orderItems = array_filter(array_map(function ($orderItem) {
            return $this->orderItemResponseParser->parse($orderItem);
        }, $entry['details']));

        $shopIdentity = $this->identityService->findOneOrThrow(
            (string)$entry['shopId'],
            ShopwareAdapter::getName(),
            Shop::getType()
        );
        $orderStatusIdentity = $this->identityService->findOneOrThrow(
            (string)$entry['orderStatusId'],
            ShopwareAdapter::getName(),
            OrderStatus::getType()
        );
        $paymentStatusIdentity = $this->identityService->findOneOrThrow(
            (string)$entry['paymentStatusId'],
            ShopwareAdapter::getName(),
            PaymentStatus::getType()
        );
        $paymentMethodIdentity = $this->identityService->findOneOrThrow(
            (string)$entry['paymentId'],
            ShopwareAdapter::getName(),
            PaymentMethod::getType()
        );
        $shippingProfileIdentity = $this->identityService->findOneOrThrow(
            (string)$entry['dispatchId'],
            ShopwareAdapter::getName(),
            ShippingProfile::getType()
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
