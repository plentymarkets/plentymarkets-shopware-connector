<?php

namespace PlentymarketsAdapter\ResponseParser\Product\Price;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\CustomerGroup\CustomerGroup;
use PlentyConnector\Connector\TransferObject\Product\Price\Price;
use PlentyConnector\Connector\TransferObject\Shop\Shop;
use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ReadApi\Account\ContactClass;
use PlentymarketsAdapter\ReadApi\Item\SalesPrice;
use Psr\Log\LoggerInterface;

/**
 * Class PriceResponseParser
 */
class PriceResponseParser implements PriceResponseParserInterface
{
    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * @var SalesPrice
     */
    private $itemsSalesPricesApi;

    /**
     * @var ContactClass
     */
    private $itemsAccountsContacsClasses;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * PriceResponseParser constructor.
     *
     * @param IdentityServiceInterface $identityService
     * @param SalesPrice               $itemsSalesPricesApi
     * @param ContactClass             $itemsAccountsContacsClasses
     * @param LoggerInterface          $logger
     */
    public function __construct(
        IdentityServiceInterface $identityService,
        SalesPrice $itemsSalesPricesApi,
        ContactClass $itemsAccountsContacsClasses,
        LoggerInterface $logger
    ) {
        $this->identityService = $identityService;
        $this->itemsSalesPricesApi = $itemsSalesPricesApi;
        $this->itemsAccountsContacsClasses = $itemsAccountsContacsClasses;
        $this->logger = $logger;
    }

    /**
     * @param array $variation
     *
     * @return Price[]
     */
    public function parse(array $variation)
    {
        static $customerGroups;

        if (null === $customerGroups) {
            $customerGroups = array_keys($this->itemsAccountsContacsClasses->findAll());
        }

        $priceConfigurations = $this->getPriceConfigurations();

        $temporaryPrices = [];
        foreach ($variation['variationSalesPrices'] as $price) {
            $priceConfiguration = array_filter($priceConfigurations, function ($configuration) use ($price) {
                return $configuration['id'] === $price['salesPriceId'];
            });

            if (empty($priceConfiguration)) {
                // no price configuration found, skip price

                continue;
            }

            $priceConfiguration = array_shift($priceConfiguration);

            $customerClasses = $priceConfiguration['customerClasses'];

            if (count($customerClasses) !== 1 && $customerClasses[0]['customerClassId'] !== -1) {
                foreach ($customerGroups as $group) {
                    $customerGroupIdentity = $this->identityService->findOneBy([
                        'adapterIdentifier' => $group,
                        'adapterName' => PlentymarketsAdapter::NAME,
                        'objectType' => CustomerGroup::TYPE,
                    ]);

                    if (null === $customerGroupIdentity) {
                        $this->logger->warning('missing mapping für customer group', ['group' => $group]);

                        continue;
                    }

                    if (!isset($temporaryPrices[$customerGroupIdentity->getObjectIdentifier()][$priceConfiguration['type']])) {
                        $temporaryPrices[$customerGroupIdentity->getObjectIdentifier()][$priceConfiguration['type']] = [
                            'from' => $priceConfiguration['minimumOrderQuantity'],
                            'price' => $price['price'],
                        ];
                    }
                }
            } else {
                if (!isset($temporaryPrices['default'][$priceConfiguration['type']])) {
                    $temporaryPrices['default'][$priceConfiguration['type']] = [
                        'from' => $priceConfiguration['minimumOrderQuantity'],
                        'price' => $price['price'],
                    ];
                }
            }
        }

        /**
         * @var Price[] $prices
         */
        $prices = [];
        foreach ($temporaryPrices as $customerGroup => $priceArray) {
            if ($customerGroup === 'default') {
                $customerGroup = null;
            }

            $price = 0.0;
            $pseudoPrice = 0.0;

            if (isset($priceArray['default'])) {
                $price = (float) $priceArray['default']['price'];
            }

            if (isset($priceArray['rrp'])) {
                $pseudoPrice = (float) $priceArray['rrp']['price'];
            }

            if (isset($priceArray['specialOffer'])) {
                if ($pseudoPrice === 0.0) {
                    $pseudoPrice = $price;
                }

                $price = (float) $priceArray['specialOffer']['price'];
            }

            $prices[] = Price::fromArray([
                'price' => $price,
                'pseudoPrice' => $pseudoPrice,
                'customerGroupIdentifier' => $customerGroup,
                'from' => (int) $priceArray['default']['from'],
                'to' => null,
            ]);
        }

        foreach ($prices as $price) {
            /**
             * @var Price[] $possibleScalePrices
             */
            $possibleScalePrices = array_filter($prices, function (Price $possiblePrice) use ($price) {
                return $possiblePrice->getCustomerGroupIdentifier() === $price->getCustomerGroupIdentifier() &&
                    spl_object_hash($price) !== spl_object_hash($possiblePrice);
            });

            if (empty($possibleScalePrices)) {
                continue;
            }

            usort($possibleScalePrices, function (Price $possibleScalePriceLeft, Price $possibleScalePriceright) {
                if ($possibleScalePriceLeft->getFromAmount() === $possibleScalePriceright->getFromAmount()) {
                    return 0;
                }

                if ($possibleScalePriceLeft->getFromAmount() > $possibleScalePriceright->getFromAmount()) {
                    return 1;
                }

                return -1;
            });

            foreach ($possibleScalePrices as $possibleScalePrice) {
                if ($possibleScalePrice->getFromAmount() > $price->getFromAmount()) {
                    $price->setToAmount($possibleScalePrice->getFromAmount() - 1);

                    break;
                }
            }
        }

        return $prices;
    }

    /**
     * Returns the matching price configurations.
     *
     * @return array
     */
    private function getPriceConfigurations()
    {
        static $priceConfigurations;

        if (null === $priceConfigurations) {
            $priceConfigurations = $this->itemsSalesPricesApi->findAll();

            $shopIdentities = $this->identityService->findBy([
                'adapterName' => PlentymarketsAdapter::NAME,
                'objectType' => Shop::TYPE,
            ]);

            if (empty($shopIdentities)) {
                return $priceConfigurations;
            }

            $priceConfigurations = array_filter($priceConfigurations, function ($priceConfiguration) use ($shopIdentities) {
                foreach ($shopIdentities as $identity) {
                    foreach ($priceConfiguration['clients'] as $client) {
                        if ($client['plentyId'] === -1 || $identity->getAdapterIdentifier() === (string) $client['plentyId']) {
                            return true;
                        }
                    }
                }

                return false;
            });

            if (empty($priceConfigurations)) {
                $this->logger->notice('no valid price configuration found');
            }
        }

        return $priceConfigurations;
    }
}
