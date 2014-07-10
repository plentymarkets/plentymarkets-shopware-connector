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
 * PlentymarketsExportControllerItemCategory provides the actual items export functionality. Like the other export
 * entities this class is called in PlentymarketsExportController.
 * The data export takes place based on plentymarkets SOAP-calls.
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsExportControllerItemCategory
{

	/**
	 *
	 * @var array
	 */
	protected $mappingShopwareID2PlentyID = array();

	/**
	 *
	 * @var array
	 */
	//protected $PLENTY_nameAndLevel2ID = array();

	/**
	 *
	 * @var array
	 */
	protected $PLENTY_CategoryTree2ShopID = array();

	/**
	 * Build the index and export the missing data to plentymarkets
	 */
	public function run()
	{
		$this->buildPlentyNameAndLevelIndex();
		//return;
		//$this->doExport();
		//$this->buildMapping();
		$this->exportCategories2Plenty(1);
	}

	/**
	 * Build an index of the existing data
	 *
	 * @todo language
	 */
	protected function buildPlentyNameAndLevelIndex()
	{
		$Request_GetItemCategoryTree = new PlentySoapRequest_GetItemCategoryTree();
		$Request_GetItemCategoryTree->Lang = 'de';
		$Request_GetItemCategoryTree->GetCategoryNames = true;

		/** @var PlentySoapResponse_GetItemCategoryTree $Response_GetItemCategoryTree */
		$Response_GetItemCategoryTree = PlentymarketsSoapClient::getInstance()->GetItemCategoryTree($Request_GetItemCategoryTree);

		if (!$Response_GetItemCategoryTree->Success)
		{
			throw new PlentymarketsExportException('The item category tree could not be retrieved', 2920);
		}

		$plenty_nameAndLevel2ID = array('children' => array());

		/** @var PlentySoapObject_ItemCategoryTreeNode $Category */
		foreach ($Response_GetItemCategoryTree->MultishopTree->item[0]->CategoryTree->item as $Category)
		{
			$index = &$plenty_nameAndLevel2ID;
			$categoryPath = explode(';', $Category->CategoryPath);
			$categoryPathNames = explode(';', $Category->CategoryPathNames);
			$branchId = 0;

			// Ist die kategorie aktiv?

			foreach ($categoryPath as $n => $categoryId)
			{
				if ($categoryId == 0)
				{
					break;
				}

				$branchId = $categoryId;
				$categoryName = $categoryPathNames[$n];
				if (!isset($index['children'][$categoryName]))
				{
					$index['children'][$categoryName] = array(
						'id' => $categoryId,
						'children' => array()
					);
				}
				$index = &$index['children'][$categoryName];
			}

			$index = array(
				'id' => $branchId
			);
		}

		$this->PLENTY_CategoryTree2ShopID = $plenty_nameAndLevel2ID;
	}
	
	private function runSoapCallForCategories($categoryName,$parentID,$level)
	{
		$Request_SetCategories = new PlentySoapRequest_SetCategories();
		
		$Request_SetCategories->SetCategories = array();
		
		for($a = 0; $a < 1; ++$a)
		{
			$RequestObject_SetCategories = new PlentySoapRequestObject_SetCategories();

			$RequestObject_SetCategories->CategoryID = null; // int

			$RequestObject_CreateCategory = new PlentySoapRequestObject_CreateCategory();
			$RequestObject_CreateCategory->Description = null; // string
			$RequestObject_CreateCategory->Description2 = null; // string
			$RequestObject_CreateCategory->FulltextActive = null; // string
			$RequestObject_CreateCategory->Image = null; // string
			$RequestObject_CreateCategory->Image1Path = null; // string
			$RequestObject_CreateCategory->Image2 = null; // string
			$RequestObject_CreateCategory->Image2Path = null; // string
			$RequestObject_CreateCategory->ItemListView = null; // string
			$RequestObject_CreateCategory->Lang = 'de'; // string
			$RequestObject_CreateCategory->LastUpdateTimestamp = null; // int
			$RequestObject_CreateCategory->LastUpdateUser = null; // string
			$RequestObject_CreateCategory->Level = $level; // int
			$RequestObject_CreateCategory->MetaDescription = null; // string
			$RequestObject_CreateCategory->MetaKeywords = null; // string
			$RequestObject_CreateCategory->MetaTitle = null; // string
			$RequestObject_CreateCategory->Name = $categoryName; // string
			$RequestObject_CreateCategory->NameURL = null; // string
			$RequestObject_CreateCategory->PageView = null; // string
			$RequestObject_CreateCategory->PlaceholderTranslation = null; // string
			$RequestObject_CreateCategory->Position = null; // int
			$RequestObject_CreateCategory->PreviewPath = null; // string
			$RequestObject_CreateCategory->RootPath = null; // string
			$RequestObject_CreateCategory->ShortDescription = null; // string
			$RequestObject_CreateCategory->SingleItemView = null; // string
			$RequestObject_CreateCategory->WebTemplateExist = null; // string
			$RequestObject_CreateCategory->WebstoreID = 1; // int
			$RequestObject_CreateCategory->ParentCategoryID = $parentID; //int
			
			$RequestObject_SetCategories->CreateCategory = $RequestObject_CreateCategory;

			$RequestObject_SetCategories->IdentificationValue = null; // string

			$RequestObject_SetCategoryBase = new PlentySoapRequestObject_SetCategoryBase();
			$RequestObject_SetCategoryBase->LinkList = null; // int
			$RequestObject_SetCategoryBase->Right = null; // string
			$RequestObject_SetCategoryBase->SiteMap = null; // int
			$RequestObject_SetCategoryBase->Type = null; // string
			$RequestObject_SetCategories->SetCategoryBase = $RequestObject_SetCategoryBase;

			$RequestObject_SetCategories->SetLinkList = null; // int
			$RequestObject_SetCategories->SetRight = null; // string
			$RequestObject_SetCategories->SetType = null; // string
			
			$Request_SetCategories->SetCategories[] = $RequestObject_SetCategories;
		}
		
		$Response_SetCategories = PlentymarketsSoapClient::getInstance()->SetCategories($Request_SetCategories);

		if (!$Response_SetCategories->Success)
		{
		    //	throw new PlentymarketsExportException('The category could not be saved! ', 2920);
			return;
			
		} else 
		{
			return $Response_SetCategories->Categories->item[0]->CategoryID;
		}

	}
	
	private function getLevel($shChild)
	{
		$level = 0;

		if (method_exists($shChild, 'getParent')) 
		{
			$parent = $shChild->getParent();
			
			while (!is_null($parent->getParentId())) 
			{
				$level++;
				$parent = $parent->getParent();
			}
		}
		
		return $level;
	}

	/**
	 * Get the mapping data: plenty category ID
	 */
	public function getPlentyCatID($shopwareID)
	{
		$row = Shopware()->Db()
			->query('
					SELECT plentyID
						FROM plenty_mapping_category 
						WHERE shopwareID = '. $shopwareID .' Limit 1'
			)
			->fetch();
		
		return intval($row['plentyID']);	
	}
	
	private function checkCatShopware2Plenty($shopwareChildren, $plentyChild)
	{
		
		if(!$plentyChild['children'])   // All shopware children have to be saved in plenty 
		{	
			foreach($shopwareChildren as $shopwareChild)
			{
				$level = $this->getLevel($shopwareChild);
	
						if($level == 1)
						{
							$plentyParentID = null;
							
						} else
						{
							$plentyParentID = $this->getPlentyCatID($shopwareChild->getParentId());
						}
	
				$plentyCatID = $this->runSoapCallForCategories($shopwareChild->getName(), $plentyParentID, $level);
	
				if(isset($plentyCatID))
				{
					PlentymarketsMappingController::addCategory($shopwareChild->getId(),$plentyCatID);

					print_r('ParentID= '.$plentyParentID.' ; Level = ' . $level . ' ; Name = ' . utf8_decode($shopwareChild->getName()) . ' has been added in plenty. '.chr(10));

					$shopwareChildren1 = $shopwareChild->getChildren();

					// search for the next children of these categories from shopware to add them in plentymarkets
					$this->checkCatShopware2Plenty($shopwareChildren1, $plentyChild);
				
				} else 
				{
					print_r('ParentID= '.$plentyParentID.' ; Level = ' . $level . ' ; Name = ' . utf8_decode($shopwareChild->getName()) . ' could not be added in plenty. '.chr(10));
				}
				
			}
		}
		else
		{	
			$catExists = false;
			
			foreach($shopwareChildren as $shopwareChild) 
			{
				foreach ($plentyChild['children'] as $name => $plentyChild1) 
				{
					if ($name == $shopwareChild->getName()) 
					{
						$catExists = true;
						// do mapping 
						PlentymarketsMappingController::addCategory($shopwareChild->getId(),$plentyChild1['id']);
						
						break;
					}
				}

				$level = $this->getLevel($shopwareChild);
				
				if ($catExists == false) 
				{
					$parentID = null;
					
					if($level > 1)
					{
						$parentID = $this->getPlentyCatID($shopwareChild->getParentId());
					}
					
					print_r('ParentID= '.$parentID.' ; Level = ' . $level . ' ; Name = ' . utf8_decode($shopwareChild->getName()) . ' has been added in plenty. '.chr(10));
					
					$plentyCatID =  $this->runSoapCallForCategories($shopwareChild->getName(), $parentID, $level);
				
					PlentymarketsMappingController::addCategory($shopwareChild->getId(),$plentyCatID);
					
				} else 
				{
					$catExists = false;
				}

					$shopwareChildren1 = $shopwareChild->getChildren();

					// search for the next categories of shopware in plentymarkets
					$this->checkCatShopware2Plenty($shopwareChildren1, $plentyChild1);	
			}
				
		}
	
	}
	
	/**
	 * Export the missing categories from shopware to plenty
	 */
	protected function exportCategories2Plenty($shopID)
	{
		$shopwareCategories = Shopware()->Models()
			->getRepository('Shopware\Models\Category\Category')
			->findBy(array('path' => null));
		
			/**
			 * @var Shopware\Models\Category\Category $Category
			 * @var Shopware\Models\Category\Category $Child2
			 * @var Shopware\Models\Category\Category $Child3
			 * @var Shopware\Models\Category\Category $Child4
			 * @var Shopware\Models\Category\Category $Child5
			 * @var Shopware\Models\Category\Category $Child6
			 * @var Shopware\Models\Category\Category $Child7
			 */

			$shopwareChildren1 = $shopwareCategories[$shopID]->getChildren();
		
			$this->checkCatShopware2Plenty($shopwareChildren1,$this->PLENTY_CategoryTree2ShopID);
		
	}
	
	/**
	 * Checks whether the export is finished
	 *
	 * @return boolean
	 */
	public function isFinished()
	{
		return true;
	}
}
