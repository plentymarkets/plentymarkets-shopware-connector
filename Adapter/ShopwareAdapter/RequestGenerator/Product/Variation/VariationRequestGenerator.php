<?php

namespace ShopwareAdapter\RequestGenerator\Product\Variation;

use ShopwareAdapter\DataProvider\CustomerGroup\CustomerGroupDataProviderInterface;
use ShopwareAdapter\ShopwareAdapter;
use SystemConnector\IdentityService\Exception\NotFoundException;
use SystemConnector\IdentityService\IdentityServiceInterface;
use SystemConnector\TransferObject\CustomerGroup\CustomerGroup;
use SystemConnector\TransferObject\Media\Media;
use SystemConnector\TransferObject\Product\Barcode\Barcode;
use SystemConnector\TransferObject\Product\Product;
use SystemConnector\TransferObject\Product\Variation\Variation;
use SystemConnector\TransferObject\Shop\Shop;
use SystemConnector\TransferObject\Unit\Unit;

class VariationRequestGenerator implements VariationRequestGeneratorInterface
{
    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * @var CustomerGroupDataProviderInterface
     */
    private $customerGroupDataProvider;

    public function __construct(
        IdentityServiceInterface $identityService,
        CustomerGroupDataProviderInterface $customerGroupDataProvider
    ) {
        $this->identityService = $identityService;
        $this->customerGroupDataProvider = $customerGroupDataProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(Variation $variation)
    {
        $unitIdentity = $this->identityService->findOneBy([
            'objectIdentifier' => $variation->getUnitIdentifier(),
            'objectType' => Unit::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
        ]);

        if (null === $unitIdentity) {
            throw new NotFoundException('Missing unit mapping - ' . $variation->getNumber());
        }

        $productIdentitiy = $this->identityService->findOneBy([
            'objectIdentifier' => $variation->getProductIdentifier(),
            'objectType' => Product::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
        ]);

        if (null === $productIdentitiy) {
            throw new NotFoundException('Missing product for variation - ' . $variation->getProductIdentifier());
        }

        $shopwareVariation = [
            'articleId' => $productIdentitiy->getAdapterIdentifier(),
            'number' => $variation->getNumber(),
            'position' => $variation->getPosition(),
            'unitId' => $unitIdentity->getAdapterIdentifier(),
            'active' => $variation->getActive(),
            'kind' => $variation->isMain() ? 1 : 2,
            'isMain' => $variation->isMain(),
            'lastStock' => $variation->hasStockLimitation(),
            'standard' => $variation->isMain(),
            'shippingtime' => $variation->getShippingTime(),
            'prices' => $this->getPrices($variation),
            'supplierNumber' => $variation->getModel(),
            'purchasePrice' => $variation->getPurchasePrice(),
            'weight' => $variation->getWeight(),
            'len' => $variation->getLength(),
            'height' => $variation->getHeight(),
            'width' => $variation->getWidth(),
            'images' => $this->getImages($variation),
            'purchaseUnit' => $variation->getContent(),
            'referenceUnit' => $variation->getReferenceAmount(),
            'minPurchase' => $variation->getMinimumOrderQuantity(),
            'purchaseSteps' => $variation->getIntervalOrderQuantity(),
            'maxPurchase' => $variation->getMaximumOrderQuantity(),
            '__options_prices' => ['replace' => true],
            '__options_images' => ['replace' => true],
        ];

        $releaseData = $variation->getReleaseDate();
        if (null !== $releaseData) {
            $shopwareVariation['releaseDate'] = $releaseData->format(DATE_W3C);
        }

        $configuratorOptions = $this->getConfiguratorOptions($variation);
        if (!empty($configuratorOptions)) {
            $shopwareVariation['configuratorOptions'] = $configuratorOptions;
        }

        /**
         * @var Barcode[] $barcodes
         */
        $barcodes = array_filter($variation->getBarcodes(), function (Barcode $barcode) {
            return $barcode->getType() === Barcode::TYPE_GTIN13;
        });

        if (!empty($barcodes)) {
            $barcode = array_shift($barcodes);

            $shopwareVariation['ean'] = $barcode->getCode();
        }

        return $shopwareVariation;
    }

    /**
     * @param Variation $variation
     *
     * @return array
     */
    private function getPrices(Variation $variation)
    {
        $prices = [];

        foreach ($variation->getPrices() as $price) {
            if (null === $price->getCustomerGroupIdentifier()) {
                $customerGroupKey = 'EK';
            } else {
                $customerGroupIdentity = $this->identityService->findOneBy([
                    'objectIdentifier' => $price->getCustomerGroupIdentifier(),
                    'objectType' => CustomerGroup::TYPE,
                    'adapterName' => ShopwareAdapter::NAME,
                ]);

                if (null === $customerGroupIdentity) {
                    continue;
                }

                $customerGroupKey = $this->customerGroupDataProvider->getCustomerGroupKeyByShopwareIdentifier(
                    $customerGroupIdentity->getAdapterIdentifier()
                );

                if (null === $customerGroupKey) {
                    continue;
                }
            }

            $prices[] = [
                'customerGroupKey' => $customerGroupKey,
                'price' => $price->getPrice(),
                'pseudoPrice' => $price->getPseudoPrice(),
                'from' => $price->getFromAmount(),
                'to' => $price->getToAmount(),
            ];
        }

        return $prices;
    }

    /**
     * @param Variation $variation
     *
     * @return array
     */
    private function getImages(Variation $variation)
    {
        $images = [];
        foreach ($variation->getImages() as $image) {
            $shopIdentifiers = array_filter($image->getShopIdentifiers(), function ($shop) {
                $identity = $this->identityService->findOneBy([
                    'objectIdentifier' => (string) $shop,
                    'objectType' => Shop::TYPE,
                    'adapterName' => ShopwareAdapter::NAME,
                ]);

                return $identity !== null;
            });

            if (empty($shopIdentifiers)) {
                continue;
            }

            $imageIdentity = $this->identityService->findOneBy([
                'objectIdentifier' => $image->getMediaIdentifier(),
                'objectType' => Media::TYPE,
                'adapterName' => ShopwareAdapter::NAME,
            ]);

            if (null === $imageIdentity) {
                continue;
            }

            $images[] = [
                'mediaId' => $imageIdentity->getAdapterIdentifier(),
                'position' => $image->getPosition(),
            ];
        }

        return $images;
    }

    /**
     * @param Variation $variation
     *
     * @return array
     */
    private function getConfiguratorOptions(Variation $variation)
    {
        $configuratorOptions = [];
        foreach ($variation->getProperties() as $property) {
            foreach ($property->getValues() as $value) {
                $configuratorOptions[] = [
                    'groupId' => null,
                    'group' => $property->getName(),
                    'optionId' => null,
                    'option' => $value->getValue(),
                ];
            }
        }

        return $configuratorOptions;
    }
}
