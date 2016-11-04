<?php

namespace ShopwareAdapter\ResponseParser;

use PlentyConnector\Connector\Identity\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\Dispatch;
use PlentyConnector\Connector\TransferObject\Order\Order;
use PlentyConnector\Connector\TransferObject\Order\OrderStatus;
use PlentyConnector\Connector\TransferObject\Order\PaymentStatus;
use PlentyConnector\Connector\TransferObject\PaymentMethod\PaymentMethod;
use PlentyConnector\Connector\TransferObject\PaymentMethod\ShippingProfile;
use PlentyConnector\Connector\TransferObject\Shop;
use ShopwareAdapter\ShopwareAdapter;

/**
 * TODO: finalize.
 *
 * Class ResponseParser
 */
class ResponseParser implements ResponseParserInterface
{
    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * ResponseParser constructor.
     *
     * @param IdentityServiceInterface $identityService
     */
    public function __construct(IdentityServiceInterface $identityService)
    {
        $this->identityService = $identityService;
    }

    /**
     * {@inheritdoc}
     */
    public function parseOrder($entry)
    {
        $identity = $this->identityService->findOrCreateIdentity(
            (string)$entry['id'],
            ShopwareAdapter::getName(),
            Order::getType()
        );

        $shopIdentity = $this->findIdentityOrThrow(Shop::getType(), (string)$entry['shopId']);
        $orderStatusIdentity = $this->findIdentityOrThrow(OrderStatus::getType(), (string)$entry['orderStatusId']);
        $paymentStatusIdentity = $this->findIdentityOrThrow(PaymentStatus::getType(), (string)$entry['paymentStatusId']);
        $paymentMethodIdentity = $this->findIdentityOrThrow(PaymentMethod::getType(), (string)$entry['paymentId']);
        $shippingProfileIdentity = $this->findIdentityOrThrow(ShippingProfile::getType(), (string)$entry['dispatchId']);

        $order = Order::fromArray([
            'identifier' => $identity->getObjectIdentifier(),
            'orderNumber' => $entry['number'],
            'orderStatusId' => $orderStatusIdentity->getObjectIdentifier(),
            'paymentStatusId' => $paymentStatusIdentity->getObjectIdentifier(),
            'paymentMethodId' => $paymentMethodIdentity->getObjectIdentifier(),
            'shippingProfileId' => $shippingProfileIdentity->getObjectIdentifier(),
            'shopId' => $shopIdentity->getObjectIdentifier(),
        ]);

        return $order;
    }

    private function findIdentityOrThrow($objectType, $adapterIdentifier) {
        $identity =  $this->identityService->findIdentity([
            'objectType' => $objectType,
            'adapterIdentifier' => $adapterIdentifier,
            'adapterName' => ShopwareAdapter::getName(),
        ]);

        if ($identity === null) {
            throw new \Exception();
        }

        return $identity;
    }
}
