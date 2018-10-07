<?php

namespace PlentymarketsAdapter\RequestGenerator\Order\OrderItem;

use PlentyConnector\Connector\ConfigService\ConfigServiceInterface;
use PlentyConnector\Connector\IdentityService\Exception\NotFoundException;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\Currency\Currency;
use PlentyConnector\Connector\TransferObject\Order\Order;
use PlentyConnector\Connector\TransferObject\Order\OrderItem\OrderItem;
use PlentyConnector\Connector\TransferObject\ShippingProfile\ShippingProfile;
use PlentyConnector\Connector\TransferObject\VatRate\VatRate;
use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use RuntimeException;

class OrderItemRequestGenerator implements OrderItemRequestGeneratorInterface
{
    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var ConfigServiceInterface
     */
    private $configService;

    public function __construct(
        IdentityServiceInterface $identityService,
        ClientInterface $client,
        ConfigServiceInterface $configService
    ) {
        $this->identityService = $identityService;
        $this->client = $client;
        $this->configService = $configService;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(OrderItem $orderItem, Order $order)
    {
        $shippingProfileIdentity = $this->identityService->findOneBy([
            'objectIdentifier' => $order->getShippingProfileIdentifier(),
            'objectType' => ShippingProfile::TYPE,
            'adapterName' => PlentymarketsAdapter::NAME,
        ]);

        if (null === $shippingProfileIdentity) {
            throw new NotFoundException('shipping profile not mapped');
        }

        $currencyIdentity = $this->identityService->findOneBy([
            'objectIdentifier' => $order->getCurrencyIdentifier(),
            'objectType' => Currency::TYPE,
            'adapterName' => PlentymarketsAdapter::NAME,
        ]);

        if (null === $currencyIdentity) {
            throw new NotFoundException('currency profile not mapped');
        }

        $itemParams = [];

        if ($orderItem->getType() === OrderItem::TYPE_PRODUCT) {
            $typeId = 1;
        } elseif ($orderItem->getType() === OrderItem::TYPE_DISCOUNT) {
            $typeId = 4;
        } elseif ($orderItem->getType() === OrderItem::TYPE_VOUCHER) {
            $typeId = 4;
        } elseif ($orderItem->getType() === OrderItem::TYPE_COUPON) {
            $typeId = 5;
        } elseif ($orderItem->getType() === OrderItem::TYPE_PAYMENT_SURCHARGE) {
            $typeId = 7;
        } elseif ($orderItem->getType() === OrderItem::TYPE_SHIPPING_COSTS) {
            $typeId = 6;
        } else {
            throw new RuntimeException('unsupported type');
        }

        // orderItemName should contain specific coupon number, to allow futher analysis
        if ($this->isCouponItem($orderItem)) {
            $itemParams['orderItemName'] = $orderItem->getNumber();
        } else {
            $itemParams['orderItemName'] = $orderItem->getName();
        }

        $itemParams['typeId'] = $typeId;
        $itemParams['quantity'] = $orderItem->getQuantity();

        if (!empty($orderItem->getNumber())) {
            $itemParams['itemVariationId'] = $this->getVariationIdentifier($orderItem);
        }

        if ($typeId === 1 && empty($itemParams['itemVariationId'])) {
            $itemParams['typeId'] = 9; // TYPE_UNASSIGEND_VARIATION;
        }

        if (null !== $orderItem->getVatRateIdentifier()) {
            $vatRateIdentity = $this->identityService->findOneBy([
                'objectIdentifier' => $orderItem->getVatRateIdentifier(),
                'objectType' => VatRate::TYPE,
                'adapterName' => PlentymarketsAdapter::NAME,
            ]);

            if (null === $vatRateIdentity) {
                throw new NotFoundException('vatRate not mapped');
            }

            $itemParams['countryVatId'] = 1;
            $itemParams['vatField'] = $vatRateIdentity->getAdapterIdentifier();
        } else {
            $itemParams['countryVatId'] = 1;
            $itemParams['vatRate'] = 0;
        }

        $itemParams['amounts'] = [
            [
                'currency' => $currencyIdentity->getAdapterIdentifier(),
                'priceOriginalGross' => $orderItem->getPrice(),
            ],
        ];

        if (null !== $shippingProfileIdentity) {
            $itemParams['properties'] = [
                [
                    'typeId' => 2,
                    'value' => (string) $shippingProfileIdentity->getAdapterIdentifier(),
                ],
            ];
        }

        $itemParams['referrerId'] = $this->configService->get('order_origin', '0.00');

        $itemParams['orderProperties'] = [];

        return $itemParams;
    }

    /**
     * @param OrderItem $orderItem
     *
     * @return int
     */
    private function getVariationIdentifier(OrderItem $orderItem)
    {
        if ($this->configService->get('variation_number_field', 'number') === 'number') {
            return $this->getVariationIdentifierFromNumber($orderItem->getNumber());
        }

        return $this->getVariationIdentifierByIdentifier($orderItem->getNumber());
    }

    /**
     * @param string $identifier
     *
     * @return int
     */
    private function getVariationIdentifierByIdentifier($identifier)
    {
        $variations = $this->client->request('GET', 'items/variations', ['id' => $identifier]);

        if (!empty($variations)) {
            $variation = array_shift($variations);

            return $variation['id'];
        }

        return 0;
    }

    /**
     * @param string $number
     *
     * @return int
     */
    private function getVariationIdentifierFromNumber($number)
    {
        $variations = $this->client->request('GET', 'items/variations', ['numberExact' => $number]);

        if (!empty($variations)) {
            $variation = array_shift($variations);

            return $variation['id'];
        }

        return 0;
    }

    /**
     * @param OrderItem $orderItem
     *
     * @return bool
     */
    private function isCouponItem(OrderItem $orderItem)
    {
        return $orderItem->getType() === OrderItem::TYPE_VOUCHER || $orderItem->getType() === OrderItem::TYPE_COUPON;
    }
}
