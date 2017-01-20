<?php

namespace ShopwareAdapter\ResponseParser\OrderItem;

use PlentyConnector\Connector\IdentityService\Exception\NotFoundException;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\OrderItem\OrderItem;
use PlentyConnector\Connector\TransferObject\Product\Product;
use PlentyConnector\Connector\TransferObject\Variation\Variation;
use Shopware\Components\Api\Manager;
use Shopware\Components\Api\Resource\Variant;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class OrderItemResponseParser
 */
class OrderItemResponseParser implements OrderItemResponseParserInterface
{
    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * OrderItemResponseParser constructor.
     *
     * @param IdentityServiceInterface $identityService
     */
    public function __construct(IdentityServiceInterface $identityService)
    {
        $this->identityService = $identityService;
    }

    /**
     * {@inheritdoc}
     *
     * @throws NotFoundException
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
            ShopwareAdapter::NAME,
            OrderItem::TYPE
        );

        $productIdentity = $this->identityService->findOneOrThrow(
            (string)$entry['articleId'],
            ShopwareAdapter::NAME,
            Product::TYPE
        );
        $variationIdentity = $this->identityService->findOneOrThrow(
            $variantId,
            ShopwareAdapter::NAME,
            Variation::TYPE
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
