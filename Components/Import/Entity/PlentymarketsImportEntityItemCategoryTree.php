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
 * Imports an item category tree
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsImportEntityItemCategoryTree
{
	/**
	 *
	 * @var array of Categories from Plenty 
	 */
	protected $plentyCategoryTree;

	/**
	 *
	 * @var Shopware\Models\Category\Repository
	 */
	protected static $CategoryRepository;

	/**
	 *
	 * @var Shopware\Components\Api\Resource\Category
	 */
	protected static $CategoryApi;

	/**
	 * I am the constructor
	 *
	 * @param PlentySoapResponse_GetItemCategoryTree $CategoryTree
	 */
	public function __construct($plentyCategoryTree)
	{
		$this->plentyCategoryTree = $plentyCategoryTree;

		if (is_null(self::$CategoryRepository))
		{
			self::$CategoryRepository = Shopware()->Models()->getRepository('Shopware\Models\Category\Category');
		}

		if (is_null(self::$CategoryApi))
		{
			self::$CategoryApi = Shopware\Components\Api\Manager::getResource('Category');
		}

	}
	
	private function updateMappingCategory($shCatId, $shShop, $plCatId, $plShop)
	{
		try {
			Shopware()->Db()->exec('REPLACE INTO plenty_mapping_category (shopwareID, plentyID) VALUES("' . $shCatId . ';' . $shShop . '", 
						 																						  "' . $plCatId . ';' . $plShop . '")');
		} catch (Exception $e) {
			// for duplicate entry 
			
		}
	}
	private function createShopwareCategory($params)
	{
		try
		{
			// Create
			$CategoryModel = self::$CategoryApi->create($params);

			// Log
			PlentymarketsLogger::getInstance()->message('Sync:Item:Category', 'The category »' . $params['name'] . '« has been created beneath the category »' . $params['parentId'] . '«');
		}
		catch (Exception $E)
		{
			// Log
			PlentymarketsLogger::getInstance()->error('Sync:Item:Category', 'The category »' . $params['name']. '« with the parentId »' . $params['parentId'] . '« could not be created (' . $E->getMessage() . ')', 3300);
		}
		
		return $CategoryModel;
	}
	/**
	 * Does the actual import
	 */
	public function import()
	{
		// get the last category id from plenty tree and get it then from plenty_mapping_category
		$plentyLastCategory = end($this->plentyCategoryTree);
		$plentyBranchId = $plentyLastCategory['BranchId'];
		
		$rows = Shopware()->Db()->query('Select * from plenty_mapping_category');
		$rows->fetch();
		$catFound = false;
		
		foreach($rows as $row)
		{
			$index = explode(';', $row['plentyID']);
			$plentyCategoryId = $index[0];
			$plentyShopId = $index[1];
			
			if($plentyCategoryId == $plentyBranchId)
			{
				$catFound = true;
				break;
			}
		}

		// if plenty branch id was found in shopware DB, get the shopware category tree and compare then both trees with each other 
		if($catFound) 
		{
			$shopwareShopId = PlentymarketsMappingController::getShopByPlentyID($plentyShopId);
			
			$shopwareCategoryTree = array();
			
			$shopwareCategoryId = PlentymarketsMappingEntityCategory::getCategoryByPlentyID($plentyCategoryId, $plentyShopId);
			
			$shopwareCategory = self::$CategoryRepository->findOneBy(array('id' => $shopwareCategoryId));

			if($shopwareCategory instanceof Shopware\Models\Category\Category)
			{
				while($shopwareCategory->getParent())
				{
					$shopwareCategoryTree[] = $shopwareCategory;
					$shopwareCategory = $shopwareCategory->getParent();
				}
			}
			
			// reverse the shopware category tree to beginn with the parent categories
			$shopwareCategoryTree = array_reverse($shopwareCategoryTree);
			
			$shopwareRootId = $shopwareCategoryTree[0]->getId();
			// remove the first parent category ( e.g. 'Deutsch') 
			unset($shopwareCategoryTree[0]);
			
			// compare then both trees with each other 
			for($i = 0; $i< count($this->plentyCategoryTree); $i++) 
			{
				if ($this->plentyCategoryTree[$i]['CategoryName'] != $shopwareCategoryTree[$i + 1]->getName() || 
					(isset($shopwareCategoryTree[$i]) && $shopwareCategoryTree[$i +1]->getParentId != $shopwareCategoryTree[$i]->getId()))  // if the shopware tree is updated
				{
					// category tree was in plenty changed and must be in shopware updated
					// 1. check if the plenty category name with the same parentId exists in shopware
					if($i == 0) // if the first Category has been changed in plenty 
					{
						$CategoryFound = self::$CategoryRepository->findOneBy(array('name' => $this->plentyCategoryTree[$i]['CategoryName'],
																					'parentId' => $shopwareRootId)); 

					} else
					{
						$CategoryFound = self::$CategoryRepository->findOneBy(array('name' => $this->plentyCategoryTree[$i]['CategoryName'],
																					'parentId' => $shopwareCategoryTree[$i]->getId())); // parentId is at position $i, the actual shopware Category is at position $i+1 

					}
					
					// 2. if category exists, change plenty_mapping_category and set shopware category
					if ($CategoryFound instanceof Shopware\Models\Category\Category) 
					{
						$this->updateMappingCategory($CategoryFound->getId(), $shopwareShopId, $this->plentyCategoryTree[$i]['BranchId'], $plentyShopId);

						$shopwareCategoryTree[$i+1] = $CategoryFound; // update shopwareCategoryTree

					} 
					else // 3. if category doesn't exist, create shopware category and change plenty_mapping_category
					{
						$params = array();
						$params['name'] = $this->plentyCategoryTree[$i]['CategoryName'];
						
						if($i == 0)
						{
							$params['parentId'] = $shopwareRootId;
						}
						else
						{
							$params['parentId'] = $shopwareCategoryTree[$i]->getId(); // TODO testen ob parentId richtig ist
						}
						
						$CategoryModel = $this->createShopwareCategory($params);
						
						if($CategoryModel instanceof Shopware\Models\Category\Category)
						{
							$this->updateMappingCategory($CategoryModel->getId(), $shopwareShopId,  $this->plentyCategoryTree[$i]['BranchId'], $plentyShopId);
							
							$shopwareCategoryTree[$i+1] = $CategoryModel;

						}
						else
						{
							return; // the category could not be created
						}
					}
					
				}
			}
			
		}
		 //  if plenty branch id was not found in shopware DB, create shopware category, change plenty_mapping_category	
	} 
}
