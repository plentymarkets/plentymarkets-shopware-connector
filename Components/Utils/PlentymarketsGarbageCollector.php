<?php
require_once PY_SOAP . 'Models/PlentySoapRequest/GetItemsByStoreID.php';

/**
 * Responsible for all clean up processes
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsGarbageCollector
{
	/**
	 * 
	 * @var integer
	 */
	const ITEM_ACTION_DEACTIVATE = 1;
	
	/**
	 * 
	 * @var integer
	 */
	const ITEM_ACTION_DELETE = 2;
	
	/**
	 * Either deletes or deactivates all shopware item that
	 * are not associated with the store id configured. 
	 */
	public function pruneItems()
	{
		// Create a temporary table
		Shopware()->Db()->exec('
			CREATE TEMPORARY TABLE IF NOT EXISTS plenty_cleanup_item
				(itemId INT UNSIGNED, INDEX (itemId))
				ENGINE = MEMORY;
		');
		
		// Get the data from plentymarkets
		$Request_GetItemsByStoreID = new PlentySoapRequest_GetItemsByStoreID();
		$Request_GetItemsByStoreID->Page = 0;
		$Request_GetItemsByStoreID->StoreID = PlentymarketsConfig::getInstance()->getStoreID();
			
		do {
			
			// Do the request
			$Response_GetItemsByStoreID = PlentymarketsSoapClient::getInstance()->GetItemsByStoreID($Request_GetItemsByStoreID);

			$itemIds = array();
			foreach ($Response_GetItemsByStoreID->Items->item as $ItemByStoreID)
			{
				$itemIds[] = $ItemByStoreID->intValue;
			}
			
			// Build the sql statement
			$itemsIdsSql = implode(', ', array_map(function ($itemId)
			{
				return sprintf('(%u)', $itemId);
			}, $itemIds));
			
			// Fill the table
			Shopware()->Db()->exec('
				INSERT INTO plenty_cleanup_item VALUES ' . $itemsIdsSql . '
			');
		
		}
		
		// Until all pages are received
		while (++$Request_GetItemsByStoreID->Page < $Response_GetItemsByStoreID->Pages);
		
		// Get the action
		$actionId = PlentymarketsConfig::getInstance()->getItemCleanupActionID(1);
		
		$where = '';
		if ($actionId == self::ITEM_ACTION_DEACTIVATE)
		{
			$where = ' AND s_articles.active = 1';
		}
		
		// Get all items, that are neither in the cleanup nor the mapping table
		$Result = Shopware()->Db()->fetchAll('
			SELECT
					id
				FROM s_articles
				WHERE
					id NOT IN (
						SELECT pmi.shopwareID
							FROM plenty_cleanup_item pci
							LEFT JOIN plenty_mapping_item pmi ON pmi.plentyID = pci.itemId
							WHERE pmi.shopwareID IS NOT NULL
					
					) '. $where .'
		');
		
		//
		$ArticleResource = \Shopware\Components\Api\Manager::getResource('Article');

		// Handle the items
		foreach ($Result as $item)
		{
			if ($actionId == self::ITEM_ACTION_DEACTIVATE)
			{
				$itemData = $ArticleResource->getOne($item['id']);
				
				// Variant
				if (isset($itemData['details']) && !empty($itemData['details']))
				{
					// Skip if already deactivated
					if (isset($itemData['mainDetail']['active']) && !$itemData['mainDetail']['active'])
					{
						continue;
					}
					
					$update = array(
						'mainDetail' => array(
							'active' => 0
						),
						'variants' => array()
					);
					
					foreach ($itemData['details'] as $variant)
					{
						$update['variants'][] = array(
							'id' => $variant['id'],
							'active' => 0
						);
					}
				}
				
				// Base item
				else
				{
					// Skip if already deactivated
					if (isset($itemData['active']) && !$itemData['active'])
					{
						continue;
					}
					
					$update = array(
						'active' => false
					);
				}
				try
				{
					$ArticleResource->update($item['id'], $update);
					PlentymarketsLogger::getInstance()->message('Cleanup:Item', 'Deactivating the item with the id ' . $item['id']);
				}
				catch (Exception $e)
				{
					PlentymarketsLogger::getInstance()->error('Cleanup:Item', 'The item with the id ' . $item['id'] . ' could not be deactivated');
					PlentymarketsLogger::getInstance()->error('Cleanup:Item', get_class($e) . ': ' . $e->getMessage());
				}
			}
			
			else if ($actionId == self::ITEM_ACTION_DELETE)
			{
				$ArticleResource->delete($item['id']);
				PlentymarketsLogger::getInstance()->message('Cleanup:Item', 'Deleting the item with the id ' . $item['id']);
			}
				
		}
		
		// Delete the temporary table
		Shopware()->Db()->exec('
			DROP TEMPORARY TABLE plenty_cleanup_item
		');
	}
}
