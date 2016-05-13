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
	protected $plentyCategoryBranch;

	/**
	 * @var integer
	 */
	protected $storeId;

	/**
	 * @var integer
	 */
	protected $shopId;
	
	/**
	 * @var string
	 */
	protected $categoryNodeLang;
	
	/**
	 * @var string
	 */
	protected $shopLang;

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
	 * @param PlentySoapObject_ItemCategoryTreeNode|PlentySoapObject_ItemCategory $categoryNode
	 * @param integer $storeId
	 * @throws Exception
	 */
	public function __construct($categoryNode, $storeId, $categoryNodeLang = 'de')
	{
		$category = array();
		if (property_exists($categoryNode, 'ItemCategoryPath'))
		{
			$categoryPath = explode(';', $categoryNode->ItemCategoryPath);
			$categoryPathNames = explode(';', $categoryNode->ItemCategoryPathNames);
		}
		else if (property_exists($categoryNode, 'CategoryPath'))
		{
			$categoryPath = explode(';', $categoryNode->CategoryPath);
			$categoryPathNames = explode(';', $categoryNode->CategoryPathNames);
		}
		else
		{
			throw new Exception();
		}

		foreach ($categoryPath as $n => $categoryId)
		{
			if ($categoryId == 0)
			{
				break;
			}

			$category[] = array(
				'branchId' => $categoryId,
				'name' => $categoryPathNames[$n]
			);
		}

		$this->plentyCategoryBranch = $category;
		$this->storeId = $storeId;
		$this->shopId = PlentymarketsMappingController::getShopByPlentyID($storeId);
		$this->categoryNodeLang = $categoryNodeLang;
	
		// get the main language of the shop set it temp
		$mainLang = array_values(PlentymarketsTranslation::getShopMainLanguage($this->shopId));
		$this->shopLang = PlentymarketsTranslation::getPlentyLocaleFormat($mainLang[0]['locale']);

		if (is_null(self::$CategoryRepository))
		{
			self::$CategoryRepository = Shopware()->Models()->getRepository('Shopware\Models\Category\Category');
		}

		if (is_null(self::$CategoryApi))
		{
			self::$CategoryApi = Shopware\Components\Api\Manager::getResource('Category');
		}
	}

	/**
	 * Does the actual import
	 */
	public function import()
	{
		$parentId = Shopware()->Models()->find('Shopware\Models\Shop\Shop', $this->shopId)->getCategory()->getId();

		// Trigger to indicate an error while creating new category
		$addError = false;
		foreach ($this->plentyCategoryBranch as $plentyCategory)
		{
			$plentyCategoryId = $plentyCategory['branchId'];
			$plentyCategoryName = $plentyCategory['name'];
			
			if($this->categoryNodeLang != $this->shopLang)
			{
				$plentyLocalCategoryName = $this->getLocalCategoryName($plentyCategoryId);
				
				if($plentyLocalCategoryName)
				{
					$plentyCategoryName = $plentyLocalCategoryName;
				}
			}		
				
			// Root category id (out of the shop)
			$CategoryFound = self::$CategoryRepository->findOneBy(array(
				'name' => $plentyCategoryName,
				'parentId' => $parentId
			));

			if ($CategoryFound instanceof Shopware\Models\Category\Category)
			{
				//
				PlentymarketsMappingEntityCategory::addCategory(
					$CategoryFound->getId(), $this->shopId, $plentyCategoryId, $this->storeId
				);

				$parentId = $CategoryFound->getId();
			}
			else
			{
				$params = array();
				$params['name'] = $plentyCategoryName;
				$params['parentId'] = $parentId;

				try
				{
					// Create
					$CategoryModel = self::$CategoryApi->create($params);

					// Add mapping and save into the object
					PlentymarketsMappingEntityCategory::addCategory(
						$CategoryModel->getId(), $this->shopId, $plentyCategoryId, $this->storeId
					);

					// Parent
					$parentCategoryName = $CategoryModel->getParent()->getName();

					// Log
					PlentymarketsLogger::getInstance()->message('Sync:Item:Category', 'The category »' . $plentyCategoryName . '« has been created beneath the category »' . $parentCategoryName . '«');

					// Id to connect with the item
					$parentId = $CategoryModel->getId();
				}
				catch (Exception $E)
				{
					// Log
					PlentymarketsLogger::getInstance()->error('Sync:Item:Category', 'The category »' . $plentyCategoryName . '« with the parentId »' . $parentId . '« could not be created (' . $E->getMessage() . ')', 3300);

					// Set the trigger - the category will not be connected with the item
					$addError = true;
				}

			}

		}
		if ($addError)
		{
			return false;
		}
		else
		{
			return $parentId;
		}
	}
	
	/*
	 * Get the category name from Plenty
	 */	
	protected function getLocalCategoryName($categoryId)
	{
		$categoryName = false;
	
		$Request_GetItemCategoryCatalog = new PlentySoapRequest_GetItemCategoryCatalog();
		$Request_GetItemCategoryCatalog->CategoryID = $categoryId;
		$Request_GetItemCategoryCatalog->Lang = $this->plentyLang;
		$Request_GetItemCategoryCatalog->StoreID = $this->storeId;

		/** @var PlentySoapResponse_GetItemCategoryCatalog $Response_GetItemCategoryCatalog */
		$Response_GetItemCategoryCatalog = PlentymarketsSoapClient::getInstance()->GetItemCategoryCatalog($Request_GetItemCategoryCatalog);

		if ($Response_GetItemCategoryCatalog->Success) 
		{
			if(is_object($Response_GetItemCategoryCatalog->Categories->item[0]))
			{
				$categoryName = $Response_GetItemCategoryCatalog->Categories->item[0]->Name;
				
				if(empty($categoryName))
				{
					$categoryName = false;
				}
			}
		}		
		
		return $categoryName;
	}
}
