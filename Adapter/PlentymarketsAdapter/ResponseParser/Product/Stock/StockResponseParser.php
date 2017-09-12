<?php

namespace PlentymarketsAdapter\ResponseParser\Product\Stock;

use PlentyConnector\Connector\ConfigService\ConfigServiceInterface;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\Product\Stock\Stock;
use PlentyConnector\Connector\TransferObject\Product\Variation\Variation;
use PlentymarketsAdapter\PlentymarketsAdapter;

/**
 * Class StockResponseParser
 */
class StockResponseParser implements StockResponseParserInterface
{
    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * @var ConfigServiceInterface
     */
    private $config;

    /**
     * StockResponseParser constructor.
     *
     * @param IdentityServiceInterface $identityService
     * @param ConfigServiceInterface   $config
     */
    public function __construct(IdentityServiceInterface $identityService, ConfigServiceInterface $config)
    {
        $this->identityService = $identityService;
        $this->config = $config;
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
            return [];
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

        return [$stock];
    }

    /**
     * @param $variation
     *
     * @return float
     */
    private function getStock($variation)
    {
        $summedStocks = 0;
        $itemWarehouse = (int) $this->config->get('item_warehouse', 0);

        foreach ($variation['stock'] as $stock) {
            if ($itemWarehouse !== 0 && $stock['warehouseId'] !== $itemWarehouse) {
                continue;
            }

            if (array_key_exists('netStock', $stock)) {
                $summedStocks += $stock['netStock'];
            }
        }

        return (float) $summedStocks;
    }
}
