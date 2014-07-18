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
	 *
	 */
	public function __construct()
	{
	}

	/**
	 *
	 */
	public function __destruct()
	{
	}

	/**
	 * Performs the actual import
	 *
	 * @param integer $lastUpdateTimestamp
	 */
	public function run()
	{
		// Get CategoryTree for store 0
		$Request_GetItemCategoryTree = new PlentySoapRequest_GetItemCategoryTree();
		$Request_GetItemCategoryTree->Lang = 'de';
		$Request_GetItemCategoryTree->GetCategoryNames = true;

		$plentyCategoryTrees = array();

		/** @var PlentySoapResponse_GetItemCategoryTree $Response_GetItemCategoryTree */
		$Response_GetItemCategoryTree = PlentymarketsSoapClient::getInstance()->GetItemCategoryTree($Request_GetItemCategoryTree);
		
		// get all category trees from plenty
		foreach ($Response_GetItemCategoryTree->MultishopTree->item[0]->CategoryTree->item as $Category) 
		{
			$index = array(
				'CategoryPath' => $Category->CategoryPath,
				'CategoryPathName' => $Category->CategoryPathNames);

			$plentyCategoryTrees[] = $index;
		}

		// do import for each plenty category tree  
		foreach ($plentyCategoryTrees as $tree) 
		{
			$categoryPath = explode(';', $tree['CategoryPath']);
			$categoryPathNames = explode(';', $tree['CategoryPathName']);
			$branchId = 0;
			$plentyCategoryTree = array();
			
			foreach ($categoryPath as $n => $categoryId) 
			{
				if ($categoryId == 0) 
				{
					break;
				}

				$categoryName = $categoryPathNames[$n];
				$branchId = $categoryId;
				$index = array(
					'BranchId' => $branchId,
					'CategoryName' => $categoryName);
				
				$plentyCategoryTree[] = $index;
			}
			
			$PlentymarketsImportEntityItemCategoryTree = new PlentymarketsImportEntityItemCategoryTree($plentyCategoryTree);
			$PlentymarketsImportEntityItemCategoryTree->import();
		}
	}
}
