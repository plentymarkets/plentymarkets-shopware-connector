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
	 * @var boolean
	 */
	protected $isRunning = false;

	/**
	 *
	 * @var PlentymarketsGarbageCollector
	 */
	protected static $Instance;

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
	 *
	 * @var integer
	 */
	const ACTION_MAPPING = 1;

	/**
	 *
	 * @var integer
	 */
	const ACTION_PRUNE_ITEMS = 2;

	/**
	 *
	 * @var integer
	 */
	const ACTION_LOG = 3;

	/**
	 * Protected constructor to prevent direct creation
	 */
	protected function __construct()
	{
	}

	/**
	 * I am the singleton method
	 *
	 * @return PlentymarketsGarbageCollector
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
	 * Runs some cleanup action
	 *
	 * @param string $action
	 */
	public function run($action)
	{
		// Quit if a process is running
		if ($this->isRunning)
		{
			return;
		}

		// Trigger running flag
		$this->isRunning = true;

		switch ($action)
		{
			case self::ACTION_MAPPING:
				$this->cleanup();
				break;

			case self::ACTION_PRUNE_ITEMS:
				$this->pruneItems();
				break;

			case self::ACTION_LOG:
				$this->cleanupLog();
				break;
		}

		$this->isRunning = false;
	}

	/**
	 * Global cleanup of the mapped data
	 */
	protected function cleanup()
	{
		$dirty = array(
			'plenty_mapping_attribute_group' => array('id', 's_article_configurator_groups'),
			'plenty_mapping_attribute_option' => array('id', 's_article_configurator_options'),
			'plenty_mapping_category' => array('id', 's_categories'),
			'plenty_mapping_country' => array('id', 's_core_countries'),
			'plenty_mapping_currency' => array('currency', 's_core_currencies'),
			'plenty_mapping_customer' => array('id', 's_order_billingaddress'),
			'plenty_mapping_customer_billing_address' => array('id', 's_user_billingaddress'),
			'plenty_mapping_customer_class' => array('id', 's_core_customergroups'),
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

		// Delete no-longer-existant-orders
		Shopware()->Db()->exec('
			DELETE FROM plenty_order WHERE shopwareId NOT IN (SELECT id FROM s_order)
		');
	}

	/**
	 * Either deletes or deactivates all shopware item that
	 * are not associated with the store id configured.
	 */
	protected function pruneItems()
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

				// Call failed
				if (is_null($Response_GetItemsByStoreID) || !property_exists($Response_GetItemsByStoreID, 'Items'))
				{
					// Log
					PlentymarketsLogger::getInstance()->error('Cleanup:Item', 'Aborting. GetItemsByStoreID apparently failed');

					// Delete the temporary table
					Shopware()->Db()->exec('
						DROP TEMPORARY TABLE plenty_cleanup_item
					');

					return;
				}

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
					PlentymarketsLogger::getInstance()->message('Cleanup:Item', 'The item with the number »' . $itemData['mainDetail']['number'] . '« will be deactivated');
				}
				catch (Exception $E)
				{
					PlentymarketsLogger::getInstance()->error('Cleanup:Item', 'The item with the number »' . $itemData['mainDetail']['number'] . '« could not be deactivated (' . $E->getMessage() . ')', 1410);
				}
			}

			else if ($actionId == self::ITEM_ACTION_DELETE)
			{
				try
				{
					$ArticleResource->delete($item['id']);
					PlentymarketsLogger::getInstance()->message('Cleanup:Item', 'The item with the number »' . $itemData['mainDetail']['number'] . '« will be deleted');
				}
				catch (Exception $E)
				{
					PlentymarketsLogger::getInstance()->error('Cleanup:Item', 'The item with the number »' . $itemData['mainDetail']['number'] . '« could not be deleted (' . $E->getMessage() . ')', 1420);
				}
			}

		}

		// Delete the temporary table
		Shopware()->Db()->exec('
			DROP TEMPORARY TABLE plenty_cleanup_item
		');
	}

	/**
	 * Cleanup of the log table
	 */
	protected function cleanupLog()
	{
		// Log
		Shopware()->Db()->exec('
			DELETE FROM plenty_log WHERE `timestamp` < '. strtotime('-1 month') .'
		');
	}
}
