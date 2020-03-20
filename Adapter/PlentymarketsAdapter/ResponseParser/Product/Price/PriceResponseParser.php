<?php

namespace PlentymarketsAdapter\ResponseParser\Product\Price;

use PlentymarketsAdapter\PlentymarketsAdapter;
use PlentymarketsAdapter\ReadApi\Account\ContactClass;
use PlentymarketsAdapter\ReadApi\Item\SalesPrice;
use Psr\Log\LoggerInterface;
use SystemConnector\ConfigService\ConfigServiceInterface;
use SystemConnector\IdentityService\IdentityServiceInterface;
use SystemConnector\IdentityService\Struct\Identity;
use SystemConnector\TransferObject\CustomerGroup\CustomerGroup;
use SystemConnector\TransferObject\Product\Price\Price;
use SystemConnector\TransferObject\Product\Variation\Variation;
use SystemConnector\TransferObject\Shop\Shop;

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
    private $itemsAccountsContactClasses;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ConfigServiceInterface
     */
    private $configService;

    public function __construct(
        IdentityServiceInterface $identityService,
        SalesPrice $itemsSalesPricesApi,
        ContactClass $itemsAccountsContactClasses,
        LoggerInterface $logger,
        ConfigServiceInterface $configService
    ) {
        $this->identityService = $identityService;
        $this->itemsSalesPricesApi = $itemsSalesPricesApi;
        $this->itemsAccountsContactClasses = $itemsAccountsContactClasses;
        $this->logger = $logger;
        $this->configService = $configService;
    }

    /**
     * @return Price[]
     */
    public function parse(array $variation): array
    {
        $temporaryPrices = $this->getPricesAsSortedArray($variation['variationSalesPrices']);

        $variationIdentity = $this->identityService->findOneBy([
            'adapterIdentifier' => (string) $variation['id'],
            'adapterName' => PlentymarketsAdapter::NAME,
            'objectType' => Variation::TYPE,
        ]);

        if (null === $variationIdentity) {
            return [];
        }

        /**
         * @var Price[] $prices
         */
        $prices = [];
        foreach ($temporaryPrices as $customerGroup => $priceArray) {
            if (!isset($priceArray['default'])) {
                continue;
            }

            if ($customerGroup === 'default') {
                $customerGroup = null;
            }

            foreach ((array) $priceArray['default'] as $salesPrice) {
                $priceIdentity = $this->identityService->findOneOrCreate(
                    $variation['id'] . '-' . $salesPrice['groupId'],
                    PlentymarketsAdapter::NAME,
                    Price::TYPE
                );

                $priceObject = new Price();
                $priceObject->setPrice($salesPrice['price']);
                $priceObject->setIdentifier($priceIdentity->getObjectIdentifier());
                $priceObject->setCustomerGroupIdentifier($customerGroup);
                $priceObject->setFromAmount($salesPrice['from']);
                $priceObject->setVariationIdentifier($variationIdentity->getObjectIdentifier());

                $this->addPseudoPrice($priceObject, $priceArray);

                $prices[] = $priceObject;
            }
        }

        foreach ($prices as $price) {
            /**
             * @var Price[] $possibleScalePrices
             */
            $possibleScalePrices = array_filter($prices, static function (Price $possiblePrice) use ($price) {
                return $possiblePrice->getCustomerGroupIdentifier() === $price->getCustomerGroupIdentifier() &&
                    spl_object_hash($price) !== spl_object_hash($possiblePrice);
            });

            if (empty($possibleScalePrices)) {
                continue;
            }

            usort($possibleScalePrices, static function (Price $possibleScalePriceLeft, Price $possibleScalePriceRight) {
                if ($possibleScalePriceLeft->getFromAmount() === $possibleScalePriceRight->getFromAmount()) {
                    return 0;
                }

                if ($possibleScalePriceLeft->getFromAmount() > $possibleScalePriceRight->getFromAmount()) {
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
     * @param int $orderOrigin
     */
    private function checkIfOriginIsInReferrers($orderOrigin, array $referrers): bool
    {
        foreach ($referrers as $referrer) {
            if (in_array($referrer['referrerId'], [-1, $orderOrigin], true)) {
                return true;
            }
        }

        return false;
    }

    private function getPricesAsSortedArray(array $variationSalesPrices = []): array
    {
        $priceConfigurations = $this->getPriceConfigurations();

        if (empty($priceConfigurations)) {
            $this->logger->notice('no valid price configuration found');

            return [];
        }

        static $customerGroups;

        if (null === $customerGroups) {
            $customerGroups = array_keys($this->itemsAccountsContactClasses->findAll());
        }

        $temporaryPrices = [];

        foreach ($variationSalesPrices as $price) {
            $priceConfiguration = $this->filterPriceConfiguration($priceConfigurations, $price);

            if (empty($priceConfiguration)) {
                // no price configuration found, skip price

                continue;
            }

            $priceConfiguration = array_shift($priceConfiguration);
            $customerClasses = (array) $priceConfiguration['customerClasses'];
            $from = (float) $priceConfiguration['minimumOrderQuantity'];

            if (count($customerClasses) === 1 && $customerClasses[0]['customerClassId'] === -1) {
                foreach ($customerGroups as $group) {
                    $customerGroupIdentity = $this->identityService->findOneBy([
                        'adapterIdentifier' => $group,
                        'adapterName' => PlentymarketsAdapter::NAME,
                        'objectType' => CustomerGroup::TYPE,
                    ]);

                    if (null === $customerGroupIdentity) {
                        continue;
                    }

                    $customerGroup = $customerGroupIdentity->getObjectIdentifier();

                    $temporaryPrices[$customerGroup][$priceConfiguration['type']][$from] = [
                        'salesPriceId' => (int) $price['salesPriceId'],
                        'from' => $from,
                        'price' => (float) $price['price'],
                        'groupId' => (float) $group,
                    ];
                }
            } else {
                foreach ($customerClasses as $group) {
                    $customerGroupIdentity = $this->identityService->findOneBy([
                        'adapterIdentifier' => $group['customerClassId'],
                        'adapterName' => PlentymarketsAdapter::NAME,
                        'objectType' => CustomerGroup::TYPE,
                    ]);

                    if (null === $customerGroupIdentity) {
                        continue;
                    }

                    $customerGroup = $customerGroupIdentity->getObjectIdentifier();

                    $temporaryPrices[$customerGroup][$priceConfiguration['type']][$from] = [
                        'salesPriceId' => (int) $price['salesPriceId'],
                        'from' => $from,
                        'price' => (float) $price['price'],
                        'groupId' => (float) $group,
                    ];
                }
            }
        }

        return $temporaryPrices;
    }

    /**
     * Returns the matching price configurations.
     */
    private function getPriceConfigurations(): array
    {
        static $priceConfigurations;

        if (null === $priceConfigurations) {
            $priceConfigurations = $this->itemsSalesPricesApi->findAll();

            $shopIdentities = $this->identityService->findBy([
                'adapterName' => PlentymarketsAdapter::NAME,
                'objectType' => Shop::TYPE,
            ]);

            $shopIdentities = array_filter($shopIdentities, function (Identity $identity) {
                $isMappedIdentity = $this->identityService->isMappedIdentity(
                    $identity->getObjectIdentifier(),
                    $identity->getObjectType(),
                    $identity->getAdapterName()
                );

                if (!$isMappedIdentity) {
                    return false;
                }

                return true;
            });

            if (empty($shopIdentities)) {
                $priceConfigurations = [];

                return $priceConfigurations;
            }

            $priceConfigurations = array_filter($priceConfigurations,
                static function ($priceConfiguration) use ($shopIdentities) {
                    foreach ($shopIdentities as $identity) {
                        foreach ((array) $priceConfiguration['clients'] as $client) {
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

    /**
     * @param array $priceArray
     */
    private function addPseudoPrice(Price $price, $priceArray)
    {
        if (isset($priceArray['rrp'][$price->getFromAmount()])) {
            $pseudoPrice = $priceArray['rrp'][$price->getFromAmount()]['price'];

            if ($pseudoPrice > $price->getPrice()) {
                $price->setPseudoPrice($pseudoPrice);
            }
        }

        if (isset($priceArray['specialOffer'][$price->getFromAmount()])) {
            $specialPrice = $priceArray['specialOffer'][$price->getFromAmount()]['price'];

            if (0.0 === $price->getPseudoPrice() && $specialPrice < $price->getPrice()) {
                $price->setPseudoPrice($price->getPrice());
            }

            $price->setPrice($priceArray['specialOffer'][$price->getFromAmount()]['price']);
        }
    }

    /**
     * @param array $priceConfigurations
     * @param array $price
     */
    private function filterPriceConfiguration($priceConfigurations, $price): array
    {
        $orderOrigin = (int) $this->configService->get('order_origin');

        $priceConfiguration = array_filter($priceConfigurations, static function ($configuration) use ($price) {
            return $configuration['id'] === $price['salesPriceId'];
        });

        if ('true' === $this->configService->get('check_price_origin')) {
            $priceConfiguration = array_filter($priceConfiguration, function ($configuration) use ($price, $orderOrigin) {
                if ($this->checkIfOriginIsInReferrers($orderOrigin, (array) $configuration['referrers'])) {
                    return $configuration['id'] === $price['salesPriceId'];
                }

                return false;
            });
        }

        return $priceConfiguration;
    }
}
