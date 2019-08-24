<?php

namespace ShopwareAdapter\ResponseParser\OrderItem;

use Doctrine\ORM\EntityRepository;
use Shopware\Models\Tax\Tax;
use ShopwareAdapter\ResponseParser\GetAttributeTrait;
use ShopwareAdapter\ShopwareAdapter;
use SystemConnector\ConfigService\ConfigServiceInterface;
use SystemConnector\IdentityService\Exception\NotFoundException;
use SystemConnector\IdentityService\IdentityServiceInterface;
use SystemConnector\TransferObject\Order\OrderItem\OrderItem;
use SystemConnector\TransferObject\VatRate\VatRate;

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
     * @var ConfigServiceInterface
     */
    private $configService;

    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    public function __construct(
        IdentityServiceInterface $identityService,
        EntityRepository $taxRepository,
        ConfigServiceInterface $configService
    ) {
        $this->identityService = $identityService;
        $this->taxRepository = $taxRepository;
        $this->configService = $configService;
    }

    /**
     * {@inheritdoc}
     *
     * @throws NotFoundException
     */
    public function parse(array $entry, $taxFree = false)
    {
        if (empty($entry['attribute'])) {
            $entry['attribute'] = [];
        }

        /**
         * @var OrderItem $orderItem
         */
        return OrderItem::fromArray([
            'type' => $this->getItemType($entry['mode']),
            'quantity' => (float) $entry['quantity'],
            'name' => $entry['articleName'],
            'number' => $entry['articleNumber'],
            'price' => $this->getPrice($entry, $taxFree),
            'vatRateIdentifier' => $this->getVatRateIdentifier($entry, $taxFree),
            'attributes' => $this->getAttributes($entry['attribute']),
        ]);
    }

    /**
     * @param array $entry
     * @param $taxFree
     *
     * @throws NotFoundException
     *
     * @return string
     */
    private function getVatRateIdentifier(array $entry, $taxFree): string
    {
        if ($taxFree || $entry['taxId'] === 0) {
            /**
             * @var null|Tax $taxModel
             */
            $taxModel = $this->taxRepository->findOneBy((float) ['tax' => $entry['taxRate']]);

            if (null === $taxModel) {
                throw new NotFoundException('no matching tax rate found - ' . $entry['taxRate']);
            }

            $entry['taxId'] = $taxModel->getId();
        }

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
                if (json_decode($this->configService->get('surcharge_as_product'), false)) {
                    return OrderItem::TYPE_PRODUCT;
                }

                return OrderItem::TYPE_PAYMENT_SURCHARGE;
            default:
                return OrderItem::TYPE_PRODUCT;
        }
    }

    /**
     * @param array $entry
     * @param bool  $taxFree
     *
     * @return float|int|mixed
     */
    private function getPrice(array $entry, $taxFree)
    {
        return $taxFree ? $entry['price'] + (($entry['price'] / 100) * $entry['taxRate']) :
            (float) $entry['price'];
    }
}
