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
 * Imports all item categories
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsImportControllerItemCategoryTree
{
	/**
	 * prepare: copy the mapping table
	 */
	public function __construct()
	{
		Shopware()->Db()->query(
			'DROP TABLE IF EXISTS plenty_mapping_category_old'
		);

		Shopware()->Db()->query(
			'CREATE TABLE plenty_mapping_category_old LIKE plenty_mapping_category'
		);

		Shopware()->Db()->query(
			'INSERT plenty_mapping_category_old SELECT * FROM plenty_mapping_category'
		);

		Shopware()->Db()->query(
			'TRUNCATE TABLE plenty_mapping_category'
		);

		// Clear the mapping cache
		PlentymarketsMappingController::clearCache('Category');
	}

	/**
	 * Cleanup the old category tree
	 */
	protected function rebuild()
	{
		$mapping = array();

		$oldMapping = Shopware()->Db()->fetchAll('
			SELECT * FROM plenty_mapping_category_old
		');

		foreach ($oldMapping as $old)
		{
			$mapping[$old['plentyID']] = array(
				'old' => $old['shopwareID']
			);
		}

		$newMapping = Shopware()->Db()->fetchAll('
			SELECT * FROM plenty_mapping_category
		');

		foreach ($newMapping as $new)
		{
			if (isset($mapping[$new['plentyID']]))
			{
				$mapping[$new['plentyID']]['new'] = $new['shopwareID'];
			}
			else
			{
				$mapping[$new['plentyID']] = array(
					'new' => $new['shopwareID']
				);
			}
		}

		foreach ($mapping as $map)
		{
			// Completely new branch
			if (!isset($map['old']))
			{
				continue;
			}

			// Deleted branch/category
			if (!isset($map['new']))
			{
				$oldParts = explode(PlentymarketsMappingEntityCategory::DELIMITER, $map['old']);
				$oldId = $oldParts[0];

				Shopware()->Db()->query('
					DELETE FROM s_articles_categories
						WHERE categoryID = ?
				', array($oldId));

				Shopware()->Db()->query('
					DELETE FROM s_categories
						WHERE id = ?
				', array($oldId));

				continue;
			}

			if ($map['new'] != $map['old'])
			{
				$newParts = explode(PlentymarketsMappingEntityCategory::DELIMITER, $map['new']);
				$newId = $newParts[0];

				$oldParts = explode(PlentymarketsMappingEntityCategory::DELIMITER, $map['old']);
				$oldId = $oldParts[0];

				Shopware()->Db()->query('
					UPDATE s_articles_categories
						SET categoryID = ?
						WHERE categoryID = ?
				', array($newId, $oldId));

				Shopware()->Db()->query('
					DELETE FROM s_categories
						WHERE id = ?
				', array($oldId));
			}
		}
	}

	/**
	 * Performs the actual import
	 *
	 * @throws PlentymarketsExportException
	 */
	public function run()
	{
		// Get the data from plentymarkets (for every mapped shop)
		$shopIds = Shopware()->Db()->fetchAll('
			SELECT plentyID FROM plenty_mapping_shop
		');

		foreach ($shopIds as $shopId)
		{
			$mainLang = array_values(PlentymarketsTranslation::getShopMainLanguage($shopId));

			$Request_GetItemCategoryTree = new PlentySoapRequest_GetItemCategoryTree();
			$Request_GetItemCategoryTree->Lang = PlentymarketsTranslation::getPlentyLocaleFormat($mainLang[0]['locale']);
			$Request_GetItemCategoryTree->GetCategoryNames = true;
			$Request_GetItemCategoryTree->StoreID = $shopId['plentyID'];
			$Request_GetItemCategoryTree->GetAktivCategories = true;

			/** @var PlentySoapResponse_GetItemCategoryTree $Response_GetItemCategoryTree */
			$Response_GetItemCategoryTree = PlentymarketsSoapClient::getInstance()->GetItemCategoryTree($Request_GetItemCategoryTree);

			if (!$Response_GetItemCategoryTree->Success)
			{
				Shopware()->Db()->query(
					'INSERT plenty_mapping_category SELECT * FROM plenty_mapping_category_old'
				);

				throw new PlentymarketsImportException('The item category tree could not be retrieved', 2920);
			}

			/** @var PlentySoapObject_ItemCategoryTreeNode $Category */
			foreach ($Response_GetItemCategoryTree->MultishopTree->item[0]->CategoryTree->item as $Category)
			{
				$importEntityItemCategoryTree = new PlentymarketsImportEntityItemCategoryTree($Category, $shopId['plentyID'], $Request_GetItemCategoryTree->Lang);
				$importEntityItemCategoryTree->import();
			}
		}

		$this->rebuild();
	}
}
