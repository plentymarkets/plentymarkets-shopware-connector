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
	 * @param array $plentyCategoryTree
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
		try
		{
			Shopware()->Db()->exec('REPLACE INTO plenty_mapping_category (shopwareID, plentyID) VALUES("' . $shCatId . ';' . $shShop . '",
						 																						  "' . $plCatId . ';' . $plShop . '")');
		}
		catch (Exception $e)
		{
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
			PlentymarketsLogger::getInstance()->error('Sync:Item:Category', 'The category »' . $params['name'] . '« with the parentId »' . $params['parentId'] . '« could not be created (' . $E->getMessage() . ')', 3300);
		}

		return $CategoryModel;
	}

	/** 
	 * @var Shopware\Models\Category\Category $shOldCategory - old shopware category
	 * @var Shopware\Models\Category\Category $shNewCategory - the last new shopware Category
	*/
	private function updateCategoryForArticles($shOldCategory, $shNewCategory)
	{
		/** @var Shopware\Models\Article\Article $article */
		foreach ($shOldCategory->getAllArticles() as $article)
		{
			$categoriesOld = $article->getCategories();
			$categoriesNew = array();

			/** @var Shopware\Models\Category\Category $categoryOld */
			foreach ($categoriesOld as $categoryOld)
			{
				if ($categoryOld->getId() == $shOldCategory->getId())
				{
					continue;
				}
				$categoriesNew[] = array('id' => $categoryOld->getId());
			}

			$categoriesNew[] = array('id' => $shNewCategory->getId());

			/** @var Shopware\Components\Api\Resource\Article */
			$resource = Shopware\Components\Api\Manager::getResource('Article');
			$resource->update($article->getId(), array('categories' => $categoriesNew));
		}
	}

	/**
	 * Does the actual import
	 */
	public function import()
	{
		// get the last category id from plenty tree and get it then from plenty_mapping_category
		$plentyLastCategory = end($this->plentyCategoryTree);
		$plentyBranchId = $plentyLastCategory['BranchId'];

		$rows = Shopware()->Db()->fetchAll('SELECT * FROM plenty_mapping_category WHERE plentyID LIKE "'. $plentyBranchId .';%"');

		foreach ($rows as $row)
		{

			$shopwareParts = explode(PlentymarketsMappingEntityCategory::DELIMITER, $row['shopwareID']);

			$shopwareCategoryId = $shopwareParts[0];
			$shopwareShopId = $shopwareParts[1];

			$plentyParts = explode(PlentymarketsMappingEntityCategory::DELIMITER, $row['plentyID']);
			$plentyShopId = $plentyParts[1];

			/** @var Shopware\Models\Category\Category[] $shopwareCategoryTree */
			$shopwareCategoryTree = array();

			/** @var Shopware\Models\Category\Category $shopwareCategory */
			$shopwareOldNode = $shopwareCategory = self::$CategoryRepository->find($shopwareCategoryId);

			if ($shopwareCategory instanceof Shopware\Models\Category\Category)
			{
				while ($shopwareCategory->getParent())
				{
					$shopwareCategoryTree[] = $shopwareCategory;
					$shopwareCategory = $shopwareCategory->getParent();
				}
			}

			// reverse the shopware category tree to begin with the parent categories
			$shopwareCategoryTree = array_reverse($shopwareCategoryTree);

			$shopwareRootId = $shopwareCategoryTree[0]->getId();
			// remove the first parent category ( e.g. 'Deutsch')
			unset($shopwareCategoryTree[0]);

			// compare then both trees with each other
			for ($i = 0; $i < count($this->plentyCategoryTree); $i++)
			{
				if (isset($shopwareCategoryTree[$i + 1]))
				{
					if (
						$this->plentyCategoryTree[$i]['CategoryName'] != $shopwareCategoryTree[$i + 1]->getName() ||
						(isset($shopwareCategoryTree[$i]) && $shopwareCategoryTree[$i + 1]->getParentId() != $shopwareCategoryTree[$i]->getId())
					) // if the shopware tree is not updated
					{
						// category tree was in plenty changed and must be in shopware updated
						// 1. check if the plenty category name with the same parentId exists in shopware
						if ($i == 0) // if the first Category has been changed in plenty
						{
							$parentId = $shopwareRootId;
						}
						else
						{
							$parentId = $shopwareCategoryTree[$i]->getId();
						}

						$CategoryFound = self::$CategoryRepository->findOneBy(
							array(
								'name' => $this->plentyCategoryTree[$i]['CategoryName'],
								'parentId' => $parentId
							)
						);

						// 2. if category exists, change plenty_mapping_category and set shopware category
						if ($CategoryFound instanceof Shopware\Models\Category\Category)
						{
							$this->updateMappingCategory($CategoryFound->getId(), $shopwareShopId, $this->plentyCategoryTree[$i]['BranchId'], $plentyShopId);

							$shopwareCategoryTree[$i + 1] = $CategoryFound; // update shopwareCategoryTree
							$shopwareCategoryTree = array_slice($shopwareCategoryTree, 0, $i + 1);

						}
						else // 3. if category doesn't exist, create shopware category and change plenty_mapping_category
						{
							$params = array();
							$params['name'] = $this->plentyCategoryTree[$i]['CategoryName'];
							$params['parentId'] = $parentId;

							$CategoryModel = $this->createShopwareCategory($params);

							if ($CategoryModel instanceof Shopware\Models\Category\Category)
							{
								$this->updateMappingCategory($CategoryModel->getId(), $shopwareShopId, $this->plentyCategoryTree[$i]['BranchId'], $plentyShopId);

								$shopwareCategoryTree[$i + 1] = $CategoryModel;
								$shopwareCategoryTree = array_slice($shopwareCategoryTree, 0, $i + 1);

							}
							else
							{
								throw new Exception();
							}
						}
					}
				}

				// if plenty tree > shopware tree, create all other categories from plenty in shopware, do mapping, update shopware tree
				else
				{
					$params = array();
					$params['name'] = $this->plentyCategoryTree[$i]['CategoryName'];
					$params['parentId'] = $shopwareCategoryTree[$i]->getId(); 

					$CategoryModel = $this->createShopwareCategory($params);

					if ($CategoryModel instanceof Shopware\Models\Category\Category)
					{
						$this->updateMappingCategory($CategoryModel->getId(), $shopwareShopId, $this->plentyCategoryTree[$i]['BranchId'], $plentyShopId);

						$shopwareCategoryTree[$i + 1] = $CategoryModel;
						$shopwareCategoryTree = array_slice($shopwareCategoryTree, 0, $i + 1);

					}
					else
					{
						throw new Exception();
					}
				}
			}
			
			// update all articles of the last shopware category, if the category id has been changed
			$shopwareNewNode = array_pop($shopwareCategoryTree);
			if($shopwareOldNode->getId() != $shopwareNewNode->getId())
			{
				$this->updateCategoryForArticles($shopwareOldNode, $shopwareNewNode);
			}
		}

	} // if plentyBranchId was not found in plenty_mapping_category, the new plenty tree will be created by article update 
}
