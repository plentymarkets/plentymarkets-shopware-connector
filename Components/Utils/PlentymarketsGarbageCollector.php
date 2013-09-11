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
	 * Global cleanup of the mapped data
	 */
	public function cleanup()
	{
		$dirty = array(
			'plenty_mapping_attribute_group' => array('id', 's_article_configurator_groups'),
			'plenty_mapping_attribute_option' => array('id', 's_article_configurator_options'),
			'plenty_mapping_category' => array('id', 's_categories'),
			'plenty_mapping_country' => array('id', 's_core_countries'),
			'plenty_mapping_currency' => array('currency', 's_core_currencies'),
			'plenty_mapping_customer' => array('id', 's_order_billingaddress'),
			'plenty_mapping_item' => array('id', 's_articles'),
			'plenty_mapping_item_variant' => array('id', 's_articles_details'),
			'plenty_mapping_measure_unit' => array('id', 's_core_units'),
			'plenty_mapping_method_of_payment' => array('id', 's_core_paymentmeans'),
			'plenty_mapping_producer' => array('id', 's_articles_supplier'),
			'plenty_mapping_property' => array('id', 's_filter_options'),
			'plenty_mapping_property_group' => array('id', 's_filter'),
			'plenty_mapping_referrer' => array('id', 's_emarketing_partner'),
			'plenty_mapping_shipping_profile' => array('id', 's_premium_dispatch'),
			'plenty_mapping_shop' => array('id', 's_core_shops'),
			'plenty_mapping_vat' => array('id', 's_core_tax')
		);
		
		foreach ($dirty as $mappingTable => $target)
		{
			Shopware()->Db()->exec('
				DELETE FROM ' . $mappingTable . ' WHERE shopwareID NOT IN (SELECT ' . $target[0] . ' FROM ' . $target[1] . ');
			');
		}
		
		// Delete non-active methods of payment
		Shopware()->Db()->exec('
			DELETE FROM plenty_mapping_method_of_payment WHERE shopwareID IN (SELECT id FROM s_core_paymentmeans WHERE active = 0)
		');
		
		// Delete non-active shipping profiles
		Shopware()->Db()->exec('
			DELETE FROM plenty_mapping_shipping_profile WHERE shopwareID IN (SELECT id FROM s_premium_dispatch WHERE active = 0)
		');
		
		// Delete non-active shops
		Shopware()->Db()->exec('
			DELETE FROM plenty_mapping_shop WHERE shopwareID IN (SELECT id FROM s_core_shops WHERE active = 0)
		');
		
		// Delete non-active partners/referrers
		Shopware()->Db()->exec('
			DELETE FROM plenty_mapping_referrer WHERE shopwareID IN (SELECT id FROM s_emarketing_partner WHERE active = 0)
		');
		
		// Log
		Shopware()->Db()->exec('
			DELETE FROM plenty_log WHERE `timestamp` < '. strtotime('-1 month') .'
		');
	}
	
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
		
		

		// Get the data from plentymarkets (for every mapped shop)
		$shopIds = Shopware()->Db()->fetchAll('
			SELECT plentyID FROM plenty_mapping_shop
		');
		
		foreach ($shopIds as $shopId)
		{
			
			$Request_GetItemsByStoreID = new PlentySoapRequest_GetItemsByStoreID();
			$Request_GetItemsByStoreID->Page = 0;
			$Request_GetItemsByStoreID->StoreID = $shopId['plentyID'];
				
			do {
				
				// Do the request
				$Response_GetItemsByStoreID = PlentymarketsSoapClient::getInstance()->GetItemsByStoreID($Request_GetItemsByStoreID);
	
				$itemIds = array();
				foreach ($Response_GetItemsByStoreID->Items->item as $ItemByStoreID)
				{
					$itemIds[] = $ItemByStoreID->intValue;
				}
				
				if (empty($itemIds))
				{
					break;
				}
				
				// Build the sql statement
				$itemsIdsSql = implode(', ', array_map(function ($itemId)
				{
					return sprintf('(%u)', $itemId);
				}, $itemIds));
				
				// Fill the table
				Shopware()->Db()->exec('
					INSERT IGNORE INTO plenty_cleanup_item VALUES ' . $itemsIdsSql . '
				');
			
			}
			
			// Until all pages are received
			while (++$Request_GetItemsByStoreID->Page < $Response_GetItemsByStoreID->Pages);
		
		}
		
		// Get the action
		$actionId = PlentymarketsConfig::getInstance()->getItemCleanupActionID(self::ITEM_ACTION_DEACTIVATE);
		
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
				try
				{
					$ArticleResource->delete($item['id']);
					PlentymarketsLogger::getInstance()->message('Cleanup:Item', 'Deleting the item with the id ' . $item['id']);
				}
				catch (Exception $e)
				{
					PlentymarketsLogger::getInstance()->error('Cleanup:Item', 'The item with the id ' . $item['id'] . ' could not be deleted');
					PlentymarketsLogger::getInstance()->error('Cleanup:Item', get_class($e) . ': ' . $e->getMessage());
				}
			}
				
		}
		
		// Delete the temporary table
		Shopware()->Db()->exec('
			DROP TEMPORARY TABLE plenty_cleanup_item
		');
	}
}
