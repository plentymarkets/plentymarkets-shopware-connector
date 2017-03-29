<?php
/**
 * plentymarkets shopware connector
 * Copyright © 2013 plentymarkets GmbH
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License, supplemented by an additional
 * permission, and of our proprietary license can be found
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "plentymarkets" is a registered trademark of plentymarkets GmbH.
 * "shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, titles and interests in the
 * above trademarks remain entirely with the trademark owners.
 *
 * @copyright  Copyright (c) 2013, plentymarkets GmbH (http://www.plentymarkets.com)
 * @author     Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */

/**
 * PlentymarketsImportEntityItemPrice provides the actual item price import functionality. Like the other import
 * entities this class is called in PlentymarketsImportController. It is important to deliver the correct price set
 * data object PlentySoapObject_ItemPriceSet to the constructor method of this class. The second parameter "$markup" is optional.
 * The data import takes place based on plentymarkets SOAP-calls.
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsImportEntityItemPrice
{
    /**
     * @var PlentySoapObject_ItemPriceSet
     */
    protected $PLENTY_PriceSet;

    /**
     * @var float
     */
    protected $PLENTY_markup = 0.0;

    /**
     * @var float
     */
    protected $referencePrice = 0.0;

    /**
     * Constructor method
     *
     * @param PlentySoapObject_ItemPriceSet $PriceSet
     * @param float $markup
     * @param float $referencePrice
     */
    public function __construct($PriceSet, $markup = 0.0, $referencePrice = 0.0)
    {
        $this->PLENTY_PriceSet = $PriceSet;
        $this->PLENTY_markup = $markup;
        $this->referencePrice = $referencePrice;

        $this->prepare();
    }

    /**
     * @return float
     */
    public function getPurchasePrice()
    {
        if (isset($this->PLENTY_PriceSet->PurchasePriceNet) && null !== $this->PLENTY_PriceSet->PurchasePriceNet) {
            return $this->PLENTY_PriceSet->PurchasePriceNet;
        }

        return 0.0;
    }

    /**
     * Returns a price array for the shopware REST api
     *
     * @return array
     */
    public function getPrices()
    {
        $prices = [];
        $customerGroupKey = PlentymarketsConfig::getInstance()->getDefaultCustomerGroupKey();

        // Prices
        if ($this->PLENTY_PriceSet->RebateLevelPrice6 > 0) {
            $price = [
                'from' => 1,
                'to' => $this->PLENTY_PriceSet->RebateLevelPrice6 - 1,
                'customerGroupKey' => $customerGroupKey,
                'price' => $this->PLENTY_PriceSet->Price,
                'percent' => 0,
            ];
            $prices[] = $price;

            for ($n = 6; $n < 12; ++$n) {
                $price = sprintf('Price%u', $n);
                $rebateLevel = sprintf('RebateLevelPrice%u', $n);
                $rebateLevelNext = sprintf('RebateLevelPrice%u', $n + 1);

                if ($this->PLENTY_PriceSet->$rebateLevel > 0) {
                    if (isset($this->PLENTY_PriceSet->$rebateLevelNext)) {
                        $to = $this->PLENTY_PriceSet->$rebateLevelNext - 1;
                    } else {
                        $to = 'beliebig';
                    }

                    $price = [
                        'customerGroupKey' => $customerGroupKey,
                        'from' => $this->PLENTY_PriceSet->$rebateLevel,
                        'to' => $to,
                        'price' => $this->PLENTY_PriceSet->$price,
                        'percent' => (($this->PLENTY_PriceSet->Price - $this->PLENTY_PriceSet->$price) / $this->PLENTY_PriceSet->Price) * 100,
                    ];
                    $prices[] = $price;
                } else {
                    break;
                }
            }
        } else {
            $price = [
                'customerGroupKey' => $customerGroupKey,
            ];

            // Reliably available starting in SOAP 111
            if (isset($this->PLENTY_PriceSet->Price) && !is_null($this->PLENTY_PriceSet->Price)) {
                $price['price'] = $this->getItemPrice($this->PLENTY_PriceSet);
            }

            // if uvp is empty, try to load from the price set, which could be the main product
            if (empty($this->referencePrice)) {
                $referencePrice = $this->PLENTY_PriceSet->RRP;
            } else {
                $referencePrice = $this->referencePrice;
            }

            // Reliably available starting in SOAP 111
            // check whether the RRP is higher than price to prevent ugly display
            if (isset($referencePrice) && !is_null($referencePrice) && isset($price['price']) && ($referencePrice > $price['price'])) {
                $price['pseudoPrice'] = $referencePrice;
            }

            $prices[] = $price;
        }

        foreach ($prices as &$price) {
            $price['price'] += $this->PLENTY_markup;
        }

        // Allow plugins to change the data
        $prices = Enlight()->Events()->filter(
            'PlentyConnector_ImportEntityItemPrice_AfterGetPrice',
            $prices,
            [
                'subject' => $this,
                'priceset' => $this->PLENTY_PriceSet,
                'markup' => $this->PLENTY_markup,
            ]
        );

        return $prices;
    }

    /**
     * Update the prices for a base item
     *
     * @param int $itemId
     *
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Shopware\Components\Api\Exception\ValidationException
     */
    public function update($itemId)
    {
        /**
         * @var \Shopware\Components\Api\Resource\Article $ArticleResource
         */
        $ArticleResource = \Shopware\Components\Api\Manager::getResource('Article');

        // Updaten
        $ArticleResource->update($itemId, [
            'mainDetail' => [
                'prices' => $this->getPrices(),
                'purchasePrice' => $this->getPurchasePrice(),
            ],
        ]);
    }

    /**
     * Update the prices for a variant
     *
     * @param int $detailId
     *
     * @throws \Shopware\Components\Api\Exception\ValidationException
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\ORMException
     *
     * @return bool
     */
    public function updateVariant($detailId)
    {
        $Detail = Shopware()->Models()->find('Shopware\Models\Article\Detail', $detailId);

        if (!$Detail instanceof Shopware\Models\Article\Detail) {
            return PlentymarketsLogger::getInstance()->error('Sync:Item:Price', 'The price of the item detail with the id »' . $detailId . '« could not be updated (item corrupt)', 3610);
        }

        $currentPrice = $this->PLENTY_PriceSet->Price + $this->PLENTY_markup;

        $Article = $Detail->getArticle();

        /**
         * @var \Shopware\Components\Api\Resource\Article $ArticleResource
         */
        $ArticleResource = \Shopware\Components\Api\Manager::getResource('Article');

        // Update
        $ArticleResource->update($Article->getId(), [
            'variants' => [
                [
                    'number' => $Detail->getNumber(),
                    'prices' => $this->getPrices(),
                    'purchasePrice' => $this->getPurchasePrice(),
                ],
            ],
        ]);

        PyLog()->message('Sync:Item:Price',
            'The price of the variant with the number »' . $Detail->getNumber() . '« has been set to »' . money_format('%.2n', $currentPrice) . '«.'
        );
    }

    /**
     * Prepared the plentymarkets price set for further use
     */
    protected function prepare()
    {
        for ($n = 6; $n < 12; ++$n) {
            $rebateLevel = sprintf('RebateLevelPrice%u', $n);
            if ($this->PLENTY_PriceSet->$rebateLevel <= 0) {
                $this->PLENTY_PriceSet->$rebateLevel = -1;
            }
        }
    }

    /**
     * Returns the item price
     *
     * @return float
     */
    protected function getItemPrice($ItemPrices)
    {
        $usePrice = PlentymarketsConfig::getInstance()->getItemPriceImportActionID(IMPORT_ITEM_PRICE);

        if ($usePrice != 'Price') {
            if (!empty($ItemPrices->{$usePrice})) {
                return $ItemPrices->{$usePrice};
            }
        }

        return $ItemPrices->Price;
    }
}
