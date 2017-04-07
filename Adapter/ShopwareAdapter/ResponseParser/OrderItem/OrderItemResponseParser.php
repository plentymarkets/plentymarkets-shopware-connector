<?php

namespace ShopwareAdapter\ResponseParser\OrderItem;

use Doctrine\ORM\EntityManagerInterface;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\Order\OrderItem\OrderItem;
use PlentyConnector\Connector\TransferObject\VatRate\VatRate;
use PlentymarketsAdapter\ResponseParser\GetAttributeTrait;
use ShopwareAdapter\ResponseParser\OrderItem\Exception\UnsupportedVatRateException;
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
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * OrderItemResponseParser constructor.
     *
     * @param IdentityServiceInterface $identityService
     * @param EntityManagerInterface   $entityManager
     */
    public function __construct(
        IdentityServiceInterface $identityService,
        EntityManagerInterface $entityManager
    ) {
        $this->identityService = $identityService;
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(array $entry, $taxFree = false)
    {
        // TODO implement other product types
        // entry mode
        // 3 : Rebate
        // 4 : Surcharge Discount;

        if (0 === $entry['mode']) {
            return $this->handleProduct($entry, $taxFree);
        }

        if (1 === $entry['mode']) {
            return $this->handleProduct($entry, $taxFree);
        }

        if (2 === $entry['mode']) {
            return $this->handleVoucher($entry, $taxFree);
        }

        return null;
    }

    /**
     * @param array $entry
     *
     * @throws UnsupportedVatRateException
     *
     * @return string
     */
    private function getVatRateIdentifier(array $entry)
    {
        $vatRateIdentity = $this->identityService->findOneBy([
            'adapterIdentifier' => (string) $entry['taxId'],
            'adapterName' => ShopwareAdapter::NAME,
            'objectType' => VatRate::TYPE,
        ]);

        if (null === $vatRateIdentity) {
            throw new UnsupportedVatRateException();
        }

        return $vatRateIdentity->getObjectIdentifier();
    }

    /**
     * @param array $entry
     * @param bool $taxFree
     *
     * @return OrderItem
     */
    private function handleProduct(array $entry, $taxFree = false)
    {
        /**
         * @var OrderItem $orderItem
         */
        $orderItem = OrderItem::fromArray([
            'type' => OrderItem::TYPE_PRODUCT,
            'quantity' => (float) $entry['quantity'],
            'name' => $entry['articleName'],
            'number' => $entry['articleNumber'],
            'price' => (float) $entry['price'],
            'vatRateIdentifier' => $taxFree ? null : $this->getVatRateIdentifier($entry),
            'attributes' => $this->getAttributes($entry['attribute']),
        ]);

        return $orderItem;
    }

    /**
     * @param array $entry
     * @param bool $taxFree
     *
     * @return OrderItem
     */
    private function handleVoucher(array $entry, $taxFree = false)
    {
        /**
         * @var OrderItem $orderItem
         */
        $orderItem = OrderItem::fromArray([
            'type' => OrderItem::TYPE_VOUCHER,
            'quantity' => (float) $entry['quantity'],
            'name' => $entry['articleName'],
            'number' => $entry['articleNumber'],
            'price' => (float) $entry['price'],
            'vatRateIdentifier' => $taxFree ? null : $this->getVatRateIdentifier($entry),
            'attributes' => $this->getAttributes($entry['attribute']),
        ]);

        return $orderItem;
    }
}
