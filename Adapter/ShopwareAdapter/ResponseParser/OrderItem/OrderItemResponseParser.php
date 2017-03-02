<?php

namespace ShopwareAdapter\ResponseParser\OrderItem;

use Assert\Assertion;
use PlentyConnector\Connector\IdentityService\Exception\NotFoundException;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\Order\OrderItem\OrderItem;
use PlentyConnector\Connector\TransferObject\Product\Product;
use PlentyConnector\Connector\TransferObject\Product\Variation\Variation;
use PlentyConnector\Connector\TransferObject\VatRate\VatRate;
use PlentymarketsAdapter\ResponseParser\GetAttributeTrait;
use Shopware\Components\Api\Manager;
use Shopware\Components\Api\Resource\Variant;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class OrderItemResponseParser
 */
class OrderItemResponseParser implements OrderItemResponseParserInterface
{
    use GetAttributeTrait;
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

        $variationIdentity = $this->getIdentifier((string)$variantId, Variation::TYPE);
        $identity = $this->getIdentifier((string)$entry['id'], OrderItem::TYPE);
        $productIdentity = $this->getIdentifier((string)$entry['articleId'], Product::TYPE);
        $taxId = $this->getIdentifier((string)$entry['taxId'], VatRate::TYPE);

        $orderItem = OrderItem::fromArray([
            'identifier' => $identity,
            'quantity' => $entry['quantity'],
            'productId' => $productIdentity,
            'variationId' => $variationIdentity,
            'name' => $entry['articleName'],
            'price' => $entry['price'],
            'attributes' => $this->getAttributes($entry['attribute']),
            'number' => $entry['number'],
            'vatRateIdentifier' => $taxId,
        ]);

        return $orderItem;
    }

    /**
     * @param int $entry
     * @return string
     */
    private function getIdentifier($entry, $type)
    {
        Assertion::integerish($entry);
        return $this->identityService->findOneOrThrow(
            (string)$entry,
            ShopwareAdapter::NAME,
            $type
        )->getObjectIdentifier();
    }

}
