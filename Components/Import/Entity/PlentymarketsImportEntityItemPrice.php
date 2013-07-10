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
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsImportEntityItemPrice
{
	/**
	 *
	 * @var PlentySoapObject_ItemPriceSet
	 */
	protected $PLENTY_PriceSet;

	/**
	 *
	 * @var float
	 */
	protected $PLENTY_markup = 0;

	/**
	 *
	 * @param PlentySoapObject_ItemPriceSet $PriceSet
	 * @param float $markup
	 */
	public function __construct($PriceSet, $markup = 0)
	{
		$this->PLENTY_PriceSet = $PriceSet;
		$this->PLENTY_markup = $markup;

		$this->prepare();
	}

	/**
	 *
	 */
	protected function prepare()
	{
		for ($n = 6; $n < 12; ++$n)
		{
			$rebateLevel = sprintf('RebateLevelPrice%u', $n);
			if ($this->PLENTY_PriceSet->$rebateLevel <= 0)
			{
				$this->PLENTY_PriceSet->$rebateLevel = -1;
			}
		}
	}

	/**
	 *
	 * @return array
	 */
	protected function getPrices()
	{
		$prices = array();

		// Prices
		if ($this->PLENTY_PriceSet->RebateLevelPrice6 > 0)
		{
			$price = array(
				'from' => 1,
				'to' => $this->PLENTY_PriceSet->RebateLevelPrice6 - 1,
				'customerGroupKey' => 'EK',
				'price' => $this->PLENTY_PriceSet->Price,
// 				'pseudoPrice' => $this->PLENTY_PriceSet->RRP,
// 				'basePrice' => $this->PLENTY_PriceSet->PurchasePriceNet,
				'percent' => 0
			);
			$prices[] = $price;

			for ($n = 6; $n < 12; ++$n)
			{
				$price = sprintf('Price%u', $n);
				$rebateLevel = sprintf('RebateLevelPrice%u', $n);
				$rebateLevelNext = sprintf('RebateLevelPrice%u', $n + 1);

				if ($this->PLENTY_PriceSet->$rebateLevel > 0)
				{
					if (isset($this->PLENTY_PriceSet->$rebateLevelNext))
					{
						$to = $this->PLENTY_PriceSet->$rebateLevelNext - 1;
					}
					else
					{
						$to = 'beliebig';
					}

					$price = array(
						'customerGroupKey' => 'EK',
						'from' => $this->PLENTY_PriceSet->$rebateLevel,
						'to' => $to,
						'price' => $this->PLENTY_PriceSet->$price,
						'percent' => (($this->PLENTY_PriceSet->Price - $this->PLENTY_PriceSet->$price) / $this->PLENTY_PriceSet->Price) * 100
					);
					$prices[] = $price;
				}
				else
				{
					break;
				}
			}
		}
		else
		{
			$price = array(
				'customerGroupKey' => 'EK',
				'price' => $this->PLENTY_PriceSet->Price,
				'pseudoPrice' => $this->PLENTY_PriceSet->RRP
			);
			$prices[] = $price;
		}

		foreach ($prices as &$price)
		{
			$price['price'] += $this->PLENTY_markup;
		}

		return $prices;
	}

	/**
	 *
	 * @param integer $itemId
	 */
	public function update($itemId)
	{
		$ArticleResource = \Shopware\Components\Api\Manager::getResource('Article');

		// Updaten
		$ArticleResource->Update($itemId, array(
			'mainDetail' => array(
				'prices' => $this->getPrices()
			)
		));
	}

	/**
	 *
	 * @param integer $detailId
	 */
	public function updateVariant($detailId)
	{
		$Detail = Shopware()->Models()
			->getRepository('Shopware\Models\Article\Detail')
			->find($detailId);

		$Article = $Detail->getArticle();

		$ArticleResource = \Shopware\Components\Api\Manager::getResource('Article');

		// Updaten
		$Article = $ArticleResource->update($Article->getId(), array(
			'variants' => array(
				array(
					'number' => $Detail->getNumber(),
					'prices' => $this->getPrices()
				)
			)
		));
	}
}
