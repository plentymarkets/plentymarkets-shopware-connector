<?php

namespace ShopwareAdapter\ResponseParser\OrderItem;

use Assert\Assertion;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\Order\OrderItem\OrderItem;
use PlentyConnector\Connector\TransferObject\VatRate\VatRate;
use PlentymarketsAdapter\ResponseParser\GetAttributeTrait;
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

        $taxId = $this->getIdentifier((string) $entry['taxId'], VatRate::TYPE);

        $orderItem = OrderItem::fromArray([
            'quantity' => $entry['quantity'],
            'name' => $entry['articleName'],
            'number' => $entry['number'],
            'price' => $entry['price'],
            'vatRateIdentifier' => $taxId,
            'attributes' => $this->getAttributes($entry['attribute']),
        ]);

        return $orderItem;
    }

    /**
     * @param int $entry
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
}
