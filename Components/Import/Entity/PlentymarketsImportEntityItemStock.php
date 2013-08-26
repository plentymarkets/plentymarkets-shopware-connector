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
 * PlentymarketsImportEntityItemStock provides the actual item stock import funcionality. Like the other import
 * entities this class is called in PlentymarketsImportController.
 * The data import takes place based on plentymarkets SOAP-calls.
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

		$itemWarehousePercentage = PlentymarketsConfig::getInstance()->getItemWarehousePercentage(100);

		if ($itemWarehousePercentage > 100 || $itemWarehousePercentage <= 0)
		{
			$itemWarehousePercentage = 100;
		}
		
		if ($stock > 0)
		{
			// At least one
			$stock = max(1, ceil($stock / 100 * $itemWarehousePercentage));
		}

		$Detail->fromArray(array(
			'inStock' => $stock
		));

		Shopware()->Models()->persist($Detail);
		Shopware()->Models()->flush();
	}
}
