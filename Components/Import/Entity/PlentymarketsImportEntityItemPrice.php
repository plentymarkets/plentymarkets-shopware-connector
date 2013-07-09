<?php

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
