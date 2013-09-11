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

require_once PY_SOAP . 'Models/PlentySoapObject/Integer.php';
require_once PY_SOAP . 'Models/PlentySoapObject/ItemAttributeMarkup.php';
require_once PY_SOAP . 'Models/PlentySoapObject/ItemAttributeValueSet.php';
require_once PY_SOAP . 'Models/PlentySoapObject/ItemAvailability.php';
require_once PY_SOAP . 'Models/PlentySoapObject/ItemBase.php';
require_once PY_SOAP . 'Models/PlentySoapObject/ItemCategory.php';
require_once PY_SOAP . 'Models/PlentySoapObject/ItemFreeTextFields.php';
require_once PY_SOAP . 'Models/PlentySoapObject/ItemOthers.php';
require_once PY_SOAP . 'Models/PlentySoapObject/ItemPriceSet.php';
require_once PY_SOAP . 'Models/PlentySoapObject/ItemProperty.php';
require_once PY_SOAP . 'Models/PlentySoapObject/ItemStock.php';
require_once PY_SOAP . 'Models/PlentySoapObject/ItemSupplier.php';
require_once PY_SOAP . 'Models/PlentySoapObject/ItemTexts.php';
require_once PY_SOAP . 'Models/PlentySoapObject/String.php';
require_once PY_SOAP . 'Models/PlentySoapRequest/GetItemsBase.php';
require_once PY_SOAP . 'Models/PlentySoapObject/ItemPriceSet.php';
require_once PY_SOAP . 'Models/PlentySoapRequestObject/GetItemsPriceLists.php';
require_once PY_SOAP . 'Models/PlentySoapRequest/GetItemsPriceLists.php';
require_once PY_SOAP . 'Models/PlentySoapObject/GetCurrentStocks.php';
require_once PY_SOAP . 'Models/PlentySoapRequestObject/GetCurrentStocks.php';
require_once PY_SOAP . 'Models/PlentySoapRequest/GetCurrentStocks.php';
require_once PY_SOAP . 'Models/PlentySoapObject/DeliveryCountryVAT.php';
require_once PY_SOAP . 'Models/PlentySoapObject/GetVATConfig.php';
require_once PY_SOAP . 'Models/PlentySoapObject/GetWarehouseList.php';
require_once PY_SOAP . 'Models/PlentySoapRequest/GetWarehouseList.php';
require_once PY_SOAP . 'Models/PlentySoapObject/GetMethodOfPayments.php';
require_once PY_SOAP . 'Models/PlentySoapObject/Integer.php';
require_once PY_SOAP . 'Models/PlentySoapRequest/GetMethodOfPayments.php';
require_once PY_SOAP . 'Models/PlentySoapObject/GetShippingProfiles.php';
require_once PY_SOAP . 'Models/PlentySoapObject/ShippingCharges.php';
require_once PY_SOAP . 'Models/PlentySoapObject/ShippingChargesCosts.php';
require_once PY_SOAP . 'Models/PlentySoapRequest/GetShippingProfiles.php';
require_once PY_SOAP . 'Models/PlentySoapObject/GetMultiShops.php';
require_once PY_SOAP . 'Models/PlentySoapRequest/GetOrderStatusList.php';
require_once PY_SOAP . 'Models/PlentySoapRequest/SearchOrders.php';
require_once PY_SOAP . 'Models/PlentySoapRequest/GetItemsPriceUpdate.php';
require_once PY_SOAP . 'Models/PlentySoapResponseObject/GetItemsPriceUpdate.php';
require_once PY_COMPONENTS . 'Import/Entity/PlentymarketsImportEntityItem.php';
require_once PY_COMPONENTS . 'Import/Entity/PlentymarketsImportEntityItemLinked.php';
require_once PY_COMPONENTS . 'Import/Entity/PlentymarketsImportEntityItemStock.php';
require_once PY_COMPONENTS . 'Import/Entity/Order/PlentymarketsImportEntityOrderAbstract.php';
require_once PY_COMPONENTS . 'Import/Entity/Order/PlentymarketsImportEntityOrderIncomingPayments.php';
require_once PY_COMPONENTS . 'Import/Entity/Order/PlentymarketsImportEntityOrderOutgoingItems.php';

/**
 * The class PlentymarketsImportController does the actual import for different cronjobs e.g. in the class PlentymarketsCronjobController.
 * It uses the different import entities in /Import/Entity respectively in /Import/Entity/Order, for example PlentymarketsImportEntityItem.
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsImportItemController
{
	/**
	 * 
	 * @var integer
	 */
	protected $startTimestamp;
	
	/**
	 * 
	 * @var integer
	 */
	protected $lastUpdateTimestamp;
	
	/**
	 * 
	 * @var integer
	 */
	protected $numberOfItems = 0;
	
	/**
	 * 
	 * @var array
	 */
	protected $itemIdsDone = array();
	
	
	/**
	 * imports the item for the given shop
	 * 
	 * @param unknown $Shop
	 */
	public function importItemsDefaultShop($Shop)
	{
		$Request_GetItemsBase = new PlentySoapRequest_GetItemsBase();
		$Request_GetItemsBase->GetAttributeValueSets = true;
		$Request_GetItemsBase->GetCategories = true;
		$Request_GetItemsBase->GetCategoryNames = true;
		$Request_GetItemsBase->GetItemAttributeMarkup = true;
		$Request_GetItemsBase->GetItemOthers = true;
		$Request_GetItemsBase->GetItemProperties = true;
		$Request_GetItemsBase->GetItemSuppliers = false;
		$Request_GetItemsBase->GetItemURL = 0;
		$Request_GetItemsBase->GetLongDescription = true;
		$Request_GetItemsBase->GetMetaDescription = false;
		$Request_GetItemsBase->GetShortDescription = true;
		$Request_GetItemsBase->GetTechnicalData = false;
		$Request_GetItemsBase->StoreID = PlentymarketsMappingController::getShopByShopwareID($Shop->getId());
		$Request_GetItemsBase->Lang = 'de';
		$Request_GetItemsBase->LastUpdateFrom = $this->lastUpdateTimestamp;
		$Request_GetItemsBase->Page = 0;
		
		PlentymarketsLogger::getInstance()->message('Sync:Item', 'LastUpdate: ' . date('r', $this->lastUpdateTimestamp));
		PlentymarketsLogger::getInstance()->message('Sync:Item', 'plentymarkets StoreID: ' . $Request_GetItemsBase->StoreID);
		PlentymarketsLogger::getInstance()->message('Sync:Item', 'shopware: ' . $Shop->getName());
		
		do
		{
		
			// Do the request
			$Response_GetItemsBase = PlentymarketsSoapClient::getInstance()->GetItemsBase($Request_GetItemsBase);

			// Quit on error
			if ($Response_GetItemsBase->Success == false)
			{
				break;
			}
		
			// Logging
			$pages = max($Response_GetItemsBase->Pages, 1);
			PlentymarketsLogger::getInstance()->message('Sync:Item', 'Page: ' . ($Request_GetItemsBase->Page + 1) . '/' . $pages);
			PlentymarketsLogger::getInstance()->message('Sync:Item', 'Received ' . count($Response_GetItemsBase->ItemsBase->item) . ' items');
		
			foreach ($Response_GetItemsBase->ItemsBase->item as $ItemBase)
			{
				try
				{
					// If this is the first run, ignore all SWAG items
					if ($Request_GetItemsBase->LastUpdateFrom == 0 && preg_match('!^Swag/\d+$!', $ItemBase->ExternalItemID))
					{
						PlentymarketsLogger::getInstance()->message('Sync:Item', 'Skipping previously exported item with the plentymarkets item id ' . $ItemBase->ItemID);
						continue;
					}
		
					$Importuer = new PlentymarketsImportEntityItem($ItemBase, $Shop);
					
					// The item has already been updated
					if (isset($this->itemIdsDone[$ItemBase->ItemID]))
					{
						// so we just need to do the categories
						$Importuer->importCategories();
					}
					else
					{
						//
						$Importuer->import();
						
						// Mark this item as done
						$this->itemIdsDone[$ItemBase->ItemID] = true;
					}
		
					// Increment the item counter for the logging
					++$this->numberOfItems;
				}
				catch (Exception $E)
				{
					PlentymarketsLogger::getInstance()->error('Sync:Item', 'Item with the plentymarkets item id ' . $ItemBase->ItemID . ' could not be importet');
					PlentymarketsLogger::getInstance()->error('Sync:Item', $E->getMessage());
				}
			}
		}
		
		// Until all pages are received
		while (++$Request_GetItemsBase->Page < $Response_GetItemsBase->Pages);
		
	}
	
	/**
	 * Finalizes the import
	 */
	protected function finish()
	{
		// Crosselling
		foreach (array_keys($this->itemIdsDone) as $itemId)
		{
			try
			{
				// Crosselling
				$ItemLinker = new PlentymarketsImportEntityItemLinked($itemId);
				$ItemLinker->link();
			}
			catch (Exception $E)
			{
				PlentymarketsLogger::getInstance()->error('Sync:Item:Linked', 'PlentymarketsImportEntityItemLinked for the item with the plentymarkets item id ' . $ItemBase->ItemID . ' failed.');
				PlentymarketsLogger::getInstance()->error('Sync:Item:Linked', $E->getMessage());
			}
		}
		
		PlentymarketsLogger::getInstance()->message('Sync:Item', $this->numberOfItems . ' items have been updated/created.');
		PlentymarketsConfig::getInstance()->setImportItemLastUpdateTimestamp($this->startTimestamp);
		
		try
		{
			// Stock stack
			PlentymarketsImportItemStockStack::getInstance()->import();
		}
		catch (Exception $E)
		{
			PlentymarketsLogger::getInstance()->error('Sync:Item:Stock', 'PlentymarketsImportItemStockStack failed');
			PlentymarketsLogger::getInstance()->error('Sync:Item:Stock', $E->getMessage());
		}
	}
	
	/**
	 *  Gets the last update timestamp and saves the current time
	 */
	public function __construct()
	{
		// Last update timestamp for the SOAP call
		$this->lastUpdateTimestamp = PlentymarketsConfig::getInstance()->getImportItemLastUpdateTimestamp(0);
		
		// The upcomming next update timestamp :)
		$this->startTimestamp = time();
	}
	
	
	/**
	 * Reads the items of plentymarkets that have changed
	 */
	public function importItems()
	{
		$ShopRepository = Shopware()->Models()->getRepository('Shopware\Models\Shop\Shop');
		$shops = $ShopRepository->findBy(array('active' => 1), array('default' => 'DESC'));
		
		foreach ($shops as $Shop)
		{
			$Shop instanceof Shopware\Models\Shop\Shop;
			$this->importItemsDefaultShop($Shop);
		}
		
		$this->finish();
	}
}
