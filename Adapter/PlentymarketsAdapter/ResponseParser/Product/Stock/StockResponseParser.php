<?php

namespace PlentymarketsAdapter\ResponseParser\Product\Stock;

use PlentymarketsAdapter\Client\ClientInterface;
use PlentymarketsAdapter\PlentymarketsAdapter;
use SystemConnector\ConfigService\ConfigServiceInterface;
use SystemConnector\IdentityService\IdentityServiceInterface;
use SystemConnector\TransferObject\Product\Stock\Stock;
use SystemConnector\TransferObject\Product\Variation\Variation;

class StockResponseParser implements StockResponseParserInterface
{
    const SALES_WAREHOUSE = 0;

    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * @var ConfigServiceInterface
     */
    private $configService;

    /**
     * @var ClientInterface
     */
    private $client;

    public function __construct(
        IdentityServiceInterface $identityService,
        ConfigServiceInterface $configService,
        ClientInterface $client
    ) {
        $this->identityService = $identityService;
        $this->configService = $configService;
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(array $variation)
    {
        $variationIdentity = $this->identityService->findOneBy([
            'adapterIdentifier' => (string) $variation['id'],
            'adapterName' => PlentymarketsAdapter::NAME,
            'objectType' => Variation::TYPE,
        ]);

        if (null === $variationIdentity) {
            return null;
        }

        $stockIdentity = $this->identityService->findOneOrCreate(
            (string) $variationIdentity->getAdapterIdentifier(),
            PlentymarketsAdapter::NAME,
            Stock::TYPE
        );

        $stock = new Stock();
        $stock->setIdentifier($stockIdentity->getObjectIdentifier());
        $stock->setVariationIdentifier($variationIdentity->getObjectIdentifier());
        $stock->setStock($this->getStock($variation));

        return $stock;
    }

    /**
     * @param array $variation
     *
     * @return float
     */
    private function getStock($variation)
    {
        $arrayStocks = [];
        $itemWarehouse = (int) $this->configService->get('item_warehouse', 0);

        static $warehouses;

        if (null === $warehouses) {
            $warehouses = $this->client->request('GET', 'stockmanagement/warehouses');

            $warehouses = array_filter($warehouses, function (array $warehouse) {
                return $warehouse['typeId'] === self::SALES_WAREHOUSE;
            });

            $warehouses = array_column($warehouses, 'id');
        }

        foreach ($variation['stock'] as $stock) {
            if (!in_array($stock['warehouseId'], $warehouses, true)) {
                continue;
            }

            if ($itemWarehouse !== 0 && $stock['warehouseId'] !== $itemWarehouse) {
                continue;
            }

            if ($stock['variationId'] !== $variation['id']) {
                continue;
            }

            $arrayStocks[] = $stock['netStock'];
        }

        return (int) array_sum($arrayStocks);
    }
}
