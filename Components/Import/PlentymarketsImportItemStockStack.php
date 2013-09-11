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

require_once PY_SOAP . 'Models/PlentySoapObject/GetCurrentStocks.php';
require_once PY_SOAP . 'Models/PlentySoapRequestObject/GetCurrentStocks.php';
require_once PY_SOAP . 'Models/PlentySoapRequest/GetCurrentStocks.php';
require_once PY_COMPONENTS . 'Import/Entity/PlentymarketsImportEntityItemStock.php';

/**
 * This is a stack of SKUs to retrieve the stocks after the import of the items.
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsImportItemStockStack
{
	/**
	 * 
	 * @var PlentymarketsImportItemStockStack
	 */
	protected static $Instance;
	
	/**
	 * 
	 * @var array[string|integer]
	 */
	protected $stack = array();
	
	/**
	 * Singleton: returns an instance
	 * 
	 * @return PlentymarketsImportItemStockStack
	 */
	public static function getInstance()
	{
		if (!self::$Instance instanceof self)
		{
			self::$Instance = new self();
		}
		return self::$Instance;
	}
	
	/**
	 * Adds the given SKU to the stack
	 * 
	 * @param string|integer $sku
	 */
	public function add($sku)
	{
		$this->stack[] = $sku;
	}
	
	/**
	 * Retrieves the stocks for the stack
	 */
	public function import()
	{
		// Unify
		$this->stack = array_unique($this->stack);
		
		if (empty($this->stack))
		{
			return;
		}		
		
		// Chunkify
		$stacks = array_chunk($this->stack, 100);
		
		// Reset
		$this->stack = array();
		
		// Warehouse
		$warehouseId = PlentymarketsConfig::getInstance()->getItemWarehouseID(0);
		
		// Build the request
		$Request_GetCurrentStocks = new PlentySoapRequest_GetCurrentStocks();
		$Request_GetCurrentStocks->Page = 0;
		
		foreach ($stacks as $stack)
		{
			// Reset
			$Request_GetCurrentStocks->Items = array();
			$numberOfStocksUpdated = 0;
			
			// Add the SKUs
			foreach ($stack as $sku)
			{
				$RequestObject_GetCurrentStocks = new PlentySoapRequestObject_GetCurrentStocks();
				$RequestObject_GetCurrentStocks->SKU = $sku;
				$Request_GetCurrentStocks->Items[] = $RequestObject_GetCurrentStocks;
			}

			// Log 
			PlentymarketsLogger::getInstance()->message('Sync:Item:Stock', 'Fetching ' . count($Request_GetCurrentStocks->Items) . ' stocks');
			
			// Do the request
			$Response_GetCurrentStocks = PlentymarketsSoapClient::getInstance()->GetCurrentStocks($Request_GetCurrentStocks);
			
			// Process
			foreach ($Response_GetCurrentStocks->CurrentStocks->item as $CurrentStock)
			{
				$CurrentStock instanceof PlentySoapObject_GetCurrentStocks;
				
				// Skip wrong warehouses
				if ($CurrentStock->WarehouseID != $warehouseId)
				{
					continue;
				}
				
				try
				{
					// Variant
					$itemDetailsID = PlentymarketsMappingController::getItemVariantByPlentyID($CurrentStock->SKU);
				}
				catch (PlentymarketsMappingExceptionNotExistant $E)
				{
					// Master item
					$parts = explode('-', $CurrentStock->SKU);
					try
					{
						$itemID = PlentymarketsMappingController::getItemByPlentyID($parts[0]);
						$Detail = Shopware()->Models()
							->getRepository('Shopware\Models\Article\Detail')
							->findOneBy(array(
								'articleId' => $itemID
						));
			
						$itemDetailsID = $Detail->getId();
					}
					catch (PlentymarketsMappingExceptionNotExistant $E)
					{
						continue;
					}
				}
				
				// Book
				PlentymarketsImportEntityItemStock::update($itemDetailsID, $CurrentStock->NetStock);
				
				// Count
				++$numberOfStocksUpdated;
			}
			
			// Log
			PlentymarketsLogger::getInstance()->message('Sync:Item:Stock', $numberOfStocksUpdated . ' stocks have been updated');
		}
		
	}
	
}
