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
 * @copyright Copyright (c) 2013, plentymarkets GmbH (http://www.plentymarkets.com)
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */


/**
 * Imports the item categories
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsImportControllerItemCategory
{
	/**
	 * Create a temporary table with better data
	 */
	public function __construct()
	{
		Shopware()->Db()->exec('
			CREATE TEMPORARY TABLE `plenty_category` (
			  `plentyId` int(11) unsigned NOT NULL,
			  `plentyPath` varchar(255) NOT NULL DEFAULT "",
			  `shopwareId` int(11) unsigned NOT NULL,
			  `size` tinyint(4) unsigned NOT NULL,
			  KEY (`plentyId`)
			) ENGINE=MEMORY
		');

		$Handle = Shopware()->Db()->query('
			SELECT * FROM plenty_mapping_category
		');

		while (($row = $Handle->fetch()) && $row)
		{
			$plentyIds = explode(';', $row['plentyID']);

			foreach ($plentyIds as $plentyId)
			{
				Shopware()->Db()->insert('plenty_category', array(
					'plentyId' => $plentyId,
					'plentyPath' => $row['plentyID'],
					'shopwareId' => $row['shopwareID'],
					'size' => count($plentyIds)
				));
			}
		}

	}

	/**
	 * Delete the helper table
	 */
	public function __destruct()
	{
		Shopware()->Db()->exec('
			DROP TEMPORARY TABLE `plenty_category`
		');
	}

	/**
	 * Performs the actual import
	 *
	 * @param integer $lastUpdateTimestamp
	 */
	public function run($lastUpdateTimestamp)
	{
		$Request_GetItemCategoryCatalogBase = new PlentySoapRequest_GetItemCategoryCatalogBase();
		$Request_GetItemCategoryCatalogBase->Lang = 'de';
		$Request_GetItemCategoryCatalogBase->LastUpdateFrom = $lastUpdateTimestamp;
		$Request_GetItemCategoryCatalogBase->Level = 1;

		do
		{

			$Request_GetItemCategoryCatalogBase->Page = 0;

			do
			{
				/** @var PlentySoapResponse_GetItemCategoryCatalogBase $Response_GetItemCategoryCatalogBase */
				$Response_GetItemCategoryCatalogBase = PlentymarketsSoapClient::getInstance()->GetItemCategoryCatalogBase($Request_GetItemCategoryCatalogBase);

				foreach ($Response_GetItemCategoryCatalogBase->Categories->item as $Category)
				{
					$PlentymarketsImportEntityItemCategory = new PlentymarketsImportEntityItemCategory($Category);
					$PlentymarketsImportEntityItemCategory->import();
				}
			}

			while (++$Request_GetItemCategoryCatalogBase->Page < $Response_GetItemCategoryCatalogBase->Pages);
		}
		while (++$Request_GetItemCategoryCatalogBase->Level <= 6);
	}
}
