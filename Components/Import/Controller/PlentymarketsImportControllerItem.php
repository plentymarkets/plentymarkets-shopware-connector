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

require_once PY_SOAP . 'Models/PlentySoapObject/ItemBase.php';
require_once PY_SOAP . 'Models/PlentySoapRequest/GetItemsBase.php';
require_once PY_COMPONENTS . 'Import/Stack/PlentymarketsImportStackItem.php';
require_once PY_COMPONENTS . 'Import/Controller/PlentymarketsImportControllerItemLinked.php';
require_once PY_COMPONENTS . 'Import/Entity/PlentymarketsImportEntityItem.php';
require_once PY_COMPONENTS . 'Import/Entity/PlentymarketsImportEntityItemStock.php';

/**
 * The class PlentymarketsImportController does the actual import for different cronjobs e.g. in the class PlentymarketsCronjobController.
 * It uses the different import entities in /Import/Entity respectively in /Import/Entity/Order, for example PlentymarketsImportEntityItem.
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsImportControllerItem
{
	/**
	 *
	 * @var integer
	 */
	const DEFAULT_CHUNK_SIZE = 250;

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
	public function importItem($itemId, $storeId)
	{
		// Check whether the item has already been imported
		$full = !isset($this->itemIdsDone[$itemId]);

		// Build the request
		$Request_GetItemsBase = new PlentySoapRequest_GetItemsBase();
		$Request_GetItemsBase->GetAttributeValueSets = $full;
		$Request_GetItemsBase->GetCategories = true;
		$Request_GetItemsBase->GetCategoryNames = true;
		$Request_GetItemsBase->GetItemAttributeMarkup = $full;
		$Request_GetItemsBase->GetItemOthers = $full;
		$Request_GetItemsBase->GetItemProperties = $full;
		$Request_GetItemsBase->GetItemSuppliers = false;
		$Request_GetItemsBase->GetItemURL = 0;
		$Request_GetItemsBase->GetLongDescription = $full;
		$Request_GetItemsBase->GetMetaDescription = false;
		$Request_GetItemsBase->GetShortDescription = $full;
		$Request_GetItemsBase->GetTechnicalData = false;
		$Request_GetItemsBase->StoreID = $storeId;
		$Request_GetItemsBase->ItemID = $itemId;
		$Request_GetItemsBase->Lang = 'de';

		// Do the request
		$Response_GetItemsBase = PlentymarketsSoapClient::getInstance()->GetItemsBase($Request_GetItemsBase);

		// On error
		if ($Response_GetItemsBase->Success == false)
		{
			// Re-add the item to the stack and quit
			PlentymarketsImportStackItem::getInstance()->addItem($ItemBase->ItemID, $storeId);
			return;
		}

		// Item not found
		if (!isset($Response_GetItemsBase->ItemsBase->item[0]))
		{
			return;
		}

		//
		$ItemBase = $Response_GetItemsBase->ItemsBase->item[0];

		try
		{
			$shopId = PlentymarketsMappingController::getShopByPlentyID($storeId);
			$Shop = Shopware()->Models()->find('Shopware\Models\Shop\Shop', $shopId);

			$Importuer = new PlentymarketsImportEntityItem($ItemBase, $Shop);

			// The item has already been updated
			if (!$full)
			{
				// so we just need to do the categories
				$Importuer->importCategories();
			}
			else
			{
				// Do a full import
				$Importuer->import();

				// Add it to the link controller
				PlentymarketsImportControllerItemLinked::getInstance()->addItem($ItemBase->ItemID);

				// Mark this item as done
				$this->itemIdsDone[$ItemBase->ItemID] = true;
			}

			// Increment the item counter for the logging
			++$this->numberOfItems;
		}

		catch (Shopware\Components\Api\Exception\ValidationException $E)
		{
			PlentymarketsLogger::getInstance()->error('Sync:Item:Validation', 'The item »'. $ItemBase->Texts->Name .'« with the id »'. $ItemBase->ItemID .'« could not be imported', 3010);
			foreach ($E->getViolations() as $ConstraintViolation)
			{
				PlentymarketsLogger::getInstance()->error('Sync:Item:Validation', $ConstraintViolation->getMessage());
				PlentymarketsLogger::getInstance()->error('Sync:Item:Validation', $ConstraintViolation->getPropertyPath() . ': ' . $ConstraintViolation->getInvalidValue());
			}

			// Re-add the item to the stack
			PlentymarketsImportStackItem::getInstance()->addItem($ItemBase->ItemID, $storeId);
		}

		catch (PlentymarketsImportItemNumberException $E)
		{
			PlentymarketsLogger::getInstance()->error('Sync:Item:Number', $E->getMessage(), $E->getCode());
		}

		catch (Exception $E)
		{
			PlentymarketsLogger::getInstance()->error('Sync:Item', 'The item »'. $ItemBase->Texts->Name .'« with the id »'. $ItemBase->ItemID .'« could not be imported', 3000);
			PlentymarketsLogger::getInstance()->error('Sync:Item', get_class($E));
			PlentymarketsLogger::getInstance()->error('Sync:Item', $E->getMessage());

			// Re-add the item to the stack
			PlentymarketsImportStackItem::getInstance()->addItem($ItemBase->ItemID, $storeId);
		}
	}

	/**
	 * Finalizes the import
	 */
	protected function finish()
	{
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

		try
		{
			// Stock stack
			PlentymarketsImportControllerItemLinked::getInstance()->run();
		}
		catch (Exception $E)
		{
			PlentymarketsLogger::getInstance()->error('Sync:Item:Linked', 'PlentymarketsImportControllerItemLinked failed');
			PlentymarketsLogger::getInstance()->error('Sync:Item:Linked', $E->getMessage());
		}
	}

	/**
	 * Reads the items of plentymarkets that have changed
	 */
	public function run()
	{
		// Number of items
		$chunkSize = PlentymarketsConfig::getInstance()->getImportItemChunkSize(self::DEFAULT_CHUNK_SIZE);

		// get the chunk out of the stack
		$chunk = PlentymarketsImportStackItem::getInstance()->getChunk($chunkSize);

		// Import each item
		foreach ($chunk as $item)
		{
			// for each assigned store
			$storeIds = explode('|', $item['storeIds']);
			foreach ($storeIds as $storeId)
			{
				// Import the item
				$this->importItem($item['itemId'], $storeId);
			}
		}

		// Log
		PlentymarketsLogger::getInstance()->message('Sync:Item', $this->numberOfItems . ' items have been updated/created.');

		// Log stack information
		$stackSize = count(PlentymarketsImportStackItem::getInstance());
		if ($stackSize == 1)
		{
			PlentymarketsLogger::getInstance()->message('Sync:Stack:Item', '1 item left in the stack');
		}
		else if ($stackSize > 1)
		{
			PlentymarketsLogger::getInstance()->message('Sync:Stack:Item', $stackSize . ' items left in the stack');
		}
		else
		{
			PlentymarketsLogger::getInstance()->message('Sync:Stack:Item', 'The stack is empty');
		}

		// Post processed
		$this->finish();
	}
}
