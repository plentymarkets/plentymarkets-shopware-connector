<?php

namespace ShopwareAdapter\ResponseParser;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\Order\Order;
use PlentyConnector\Connector\TransferObject\OrderItem\OrderItem;
use PlentyConnector\Connector\TransferObject\OrderStatus\OrderStatus;
use PlentyConnector\Connector\TransferObject\PaymentMethod\PaymentMethod;
use PlentyConnector\Connector\TransferObject\PaymentStatus\PaymentStatus;
use PlentyConnector\Connector\TransferObject\Product\Product;
use PlentyConnector\Connector\TransferObject\ShippingProfile\ShippingProfile;
use PlentyConnector\Connector\TransferObject\Shop\Shop;
use PlentyConnector\Connector\TransferObject\TransferObjectInterface;
use PlentyConnector\Connector\TransferObject\Variation\Variation;
use Shopware\Components\Api\Manager;
use Shopware\Components\Api\Resource\Variant;
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
            ShopwareAdapter::NAME,
            Order::TYPE
        );

        $orderItems = array_map(array($this, 'parseOrderItem'), $entry['details']);

        $shopIdentity = $this->findIdentityOrThrow(Shop::TYPE, (string)$entry['shopId']);
        $orderStatusIdentity = $this->findIdentityOrThrow(OrderStatus::TYPE, (string)$entry['orderStatusId']);
        $paymentStatusIdentity = $this->findIdentityOrThrow(PaymentStatus::TYPE,
            (string)$entry['paymentStatusId']);
        $paymentMethodIdentity = $this->findIdentityOrThrow(PaymentMethod::TYPE, (string)$entry['paymentId']);
        $shippingProfileIdentity = $this->findIdentityOrThrow(ShippingProfile::TYPE, (string)$entry['dispatchId']);

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

    private function findIdentityOrThrow($objectType, $adapterIdentifier)
    {
        $identity = $this->identityService->findIdentity([
            'objectType' => $objectType,
            'adapterIdentifier' => $adapterIdentifier,
            'adapterName' => ShopwareAdapter::NAME,
        ]);

        if ($identity === null) {
            // TODO add proper exception
            throw new \Exception();
        }

        return $identity;
    }

    public function parseOrderItem($entry)
    {
        // entry mode
        // 0 : Product
        // 1 : Premium Product (Prämie)
        // 2 : Voucher
        // 3 : Rebate
        // 4 : Surcharge Discount
        if ($entry['mode'] > 0) {
            // TODO implement other product types
            return null;
        }

        /**
         * @var Variant $variantResource
         */
        $variantResource = Manager::getResource('variant');
        $variantId = $variantResource->getIdFromNumber($entry['articleNumber']);

        $identity = $this->identityService->findOrCreateIdentity(
            (string)$entry['id'],
            ShopwareAdapter::NAME,
            OrderItem::TYPE
        );

        $productIdentity = $this->findIdentityOrThrow(Product::TYPE, (string)$entry['articleId']);
        $variationIdentity = $this->findIdentityOrThrow(Variation::TYPE, $variantId);

        $orderItem = OrderItem::fromArray([
            'identifier' => $identity->getObjectIdentifier(),
            'quantity' => $entry['quantity'],
            'productId' => $productIdentity->getObjectIdentifier(),
            'variationId' => $variationIdentity->getObjectIdentifier(),
            'name' => $entry['articleName'],
            'price' => $entry['price'],
        ]);

        return $orderItem;
    }

    /**
     * @param array $entry
     *
     * @return TransferObjectInterface
     */
    public function parse(array $entry)
    {
        // TODO: Implement parse() method.
    }
}
