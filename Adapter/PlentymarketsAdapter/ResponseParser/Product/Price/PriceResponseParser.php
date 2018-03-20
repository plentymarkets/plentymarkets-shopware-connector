<?php

namespace PlentymarketsAdapter\ResponseParser\Product\Price;

use PlentyConnector\Connector\ConfigService\ConfigService;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\CustomerGroup\CustomerGroup;
use PlentyConnector\Connector\TransferObject\Product\Price\Price;
use PlentyConnector\Connector\TransferObject\Shop\Shop;
use PlentyConnector\Connector\ValueObject\Identity\Identity;
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
        $temporaryPrices = $this->getPricesAsSortedArray($variation['variationSalesPrices']);

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

            foreach ($priceArray['default'] as $salesPrice) {
                $priceObject = new Price();
                $priceObject->setPrice($salesPrice['price']);
                $priceObject->setCustomerGroupIdentifier($customerGroup);
                $priceObject->setFromAmount($salesPrice['from']);

                $this->addPseudoPrice($priceObject, $priceArray);

                $prices[] = $priceObject;
            }
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
     * @param $orderOrigin
     * @param $referrers
     * @return bool
     */
    private function checkIfOriginIsInReferrers($orderOrigin, $referrers)
    {
        foreach ($referrers as $referrer) {
           if ($referrer['salesPriceId'] == $orderOrigin) {
               return true;
           }
        }

        return false;
    }

    /**
     * @return array
     */
    private function getOriginConfig()
    {
        /** @var ConfigService $config */
        $config = Shopware()->Container()->get('plenty_connector.config');
        $checkOrigin = $config->get('check_price_origin', false);
        $orderOrigin = $config->get('order_origin');

        return [$checkOrigin, $orderOrigin];
    }

    /**
     * @param array $variationSalesPrices
     *
     * @return array
     */
    private function getPricesAsSortedArray(array $variationSalesPrices = [])
    {
        $priceConfigurations = $this->getPriceConfigurations();

        if (empty($priceConfigurations)) {
            $this->logger->notice('no valid price configuration found');

            return [];
        }

        static $customerGroups;

        if (null === $customerGroups) {
            $customerGroups = array_keys($this->itemsAccountsContacsClasses->findAll());
        }

        $temporaryPrices = [];

        foreach ($variationSalesPrices as $price) {

            $priceConfiguration = array_filter($priceConfigurations, function ($configuration) use ($price) {

                // shall we check for the configured origin?
                list($checkOrigin, $orderOrigin) = $this->getOriginConfig();

                if($checkOrigin) {
                    if($this->checkIfOriginIsInReferrers($orderOrigin, $configuration['referrers'])) {
                        return $configuration['id'] === $price['salesPriceId'];
                    } else {
                        return false;
                    }
                } else {
                    return $configuration['id'] === $price['salesPriceId'];
                }
            });

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
                        'from' => $from,
                        'price' => (float) $price['price'],
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
                        'from' => $from,
                        'price' => (float) $price['price'],
                    ];
                }
            }
        }

        return $temporaryPrices;
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

            $shopIdentities = array_filter($shopIdentities, function (Identity $identity) {
                $isMappedIdentity = $this->identityService->isMapppedIdentity(
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
                function ($priceConfiguration) use ($shopIdentities) {
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

    /**
     * @param Price $price
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
}
