<?php

namespace ShopwareAdapter\ResponseParser\OrderItem;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\OrderItem\OrderItem;
use PlentyConnector\Connector\TransferObject\Product\Product;
use PlentyConnector\Connector\TransferObject\Variation\Variation;
use Shopware\Components\Api\Manager;
use Shopware\Components\Api\Resource\Variant;
use ShopwareAdapter\ResponseParser\ResponseParserInterface;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class OrderItemResponseParser
 */
class OrderItemResponseParser implements ResponseParserInterface
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
    public function parse(array $entry)
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

        $identity = $this->identityService->findOneOrCreate(
            (string)$entry['id'],
            ShopwareAdapter::getName(),
            OrderItem::getType()
        );

        $productIdentity = $this->identityService->findOneOrThrow(
            (string)$entry['articleId'],
            ShopwareAdapter::getName(),
            Product::getType()
        );
        $variationIdentity = $this->identityService->findOneOrThrow(
            $variantId,
            ShopwareAdapter::getName(),
            Variation::getType()
        );

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
}
