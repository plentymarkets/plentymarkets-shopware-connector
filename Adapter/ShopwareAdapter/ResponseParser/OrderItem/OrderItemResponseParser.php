<?php

namespace ShopwareAdapter\ResponseParser\OrderItem;

use Doctrine\ORM\EntityRepository;
use PlentyConnector\Connector\IdentityService\Exception\NotFoundException;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\Order\OrderItem\OrderItem;
use PlentyConnector\Connector\TransferObject\VatRate\VatRate;
use Shopware\Models\Tax\Tax;
use ShopwareAdapter\ResponseParser\GetAttributeTrait;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class OrderItemResponseParser
 */
class OrderItemResponseParser implements OrderItemResponseParserInterface
{
    use GetAttributeTrait;

    const ITEM_TYPE_ID_VOUCHER = 2;
    const ITEM_TYPE_ID_DISCOUNT = 3;
    const ITEM_TYPE_ID_SURCHARGE = 4;

    /**
     * @var EntityRepository
     */
    private $taxRepository;

    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * OrderItemResponseParser constructor.
     *
     * @param IdentityServiceInterface $identityService
     * @param EntityRepository $taxRepository
     */
    public function __construct(IdentityServiceInterface $identityService, EntityRepository $taxRepository)
    {
        $this->identityService = $identityService;
        $this->taxRepository = $taxRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(array $entry, $taxFree = false)
    {
        /**
         * @var OrderItem $orderItem
         */
        $orderItem = OrderItem::fromArray([
            'type' => $this->getItemType($entry['mode']),
            'quantity' => (float) $entry['quantity'],
            'name' => $entry['articleName'],
            'number' => $entry['articleNumber'],
            'price' => $this->getPrice($entry, $taxFree),
            'vatRateIdentifier' => $this->getVatRateIdentifier($entry, $taxFree),
            'attributes' => $this->getAttributes($entry['attribute']),
        ]);

        return $orderItem;
    }

    /**
     * @param array $entry
     *
     * @throws NotFoundException
     *
     * @return null|string
     */
    private function getVatRateIdentifier(array $entry, $taxFree)
    {
        /**
         * @var Tax $taxModel
         */
        $taxModel = $this->taxRepository->findOneBy(['tax' => $entry['taxRate']]);

        if (null === $taxModel) {
            throw new \InvalidArgumentException('no matching tax rate found - ' . $entry['taxRate']);
        }

        $entry['taxId'] = $taxModel->getId();

        $vatRateIdentity = $this->identityService->findOneBy([
            'adapterIdentifier' => (string) $entry['taxId'],
            'adapterName' => ShopwareAdapter::NAME,
            'objectType' => VatRate::TYPE,
        ]);

        if (null === $vatRateIdentity) {
            throw new NotFoundException('missing vat rate identity for taxId ' . $entry['taxId']);
        }

        return $vatRateIdentity->getObjectIdentifier();
    }

    /**
     * @param int $mode
     *
     * @return int
     */
    private function getItemType($mode)
    {
        switch ($mode) {
            case self::ITEM_TYPE_ID_VOUCHER:
                return OrderItem::TYPE_VOUCHER;
            case self::ITEM_TYPE_ID_DISCOUNT:
                return OrderItem::TYPE_DISCOUNT;
            case self::ITEM_TYPE_ID_SURCHARGE:
                return OrderItem::TYPE_PAYMENT_SURCHARGE;
            default:
                return OrderItem::TYPE_PRODUCT;
        }
    }

    /**
     * @param array $entry
     * @param $taxFree
     *
     * @return float|int|mixed
     */
    private function getPrice(array $entry, $taxFree)
    {
        return $taxFree ? $entry['price'] + (($entry['price'] / 100) * $entry['taxRate']) :
            (float) $entry['price'];
    }
}
