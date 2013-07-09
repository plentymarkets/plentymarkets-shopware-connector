<?php

/**
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 *
 */
class PlentymarketsImportEntityItemStock
{

	/**
	 *
	 * @param integer $itemDetailsID        	
	 * @param float $stock        	
	 */
	public static function update($itemDetailsID, $stock)
	{
		$Detail = Shopware()->Models()
			->getRepository('Shopware\Models\Article\Detail')
			->find($itemDetailsID);
		
		$Detail->fromArray(array(
			'inStock' => $stock
		));
		
		Shopware()->Models()->persist($Detail);
		Shopware()->Models()->flush();
	}
}
