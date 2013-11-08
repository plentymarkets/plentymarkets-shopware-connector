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

require_once PY_SOAP . 'Models/PlentySoapRequest/GetItemsUpdated.php';

/**
 * Handles the item import stack
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsImportStackItem implements Countable
{

	/**
	 *
	 * @var PlentymarketsImportStackItem
	 */
	protected static $Instance;

	/**
	 *
	 * @var integer
	 */
	protected $size;

	/**
	 * I am a Singleton
	 *
	 * @return PlentymarketsImportStackItem
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
	 * Adds an item to the stack
	 *
	 * @param integer $itemId
	 * @param integer $storeId
	 */
	public function addItem($itemId, $storeId)
	{
		try
		{
			Shopware()->Db()->insert(
				'plenty_stack_item',
				array(
					'itemId' => $itemId,
					'timestamp' => time(),
					'storeIds' => $storeId
				)
			);
		}
		catch (Exception $E)
		{
			// Get the entry
			$stackedItem = Shopware()->Db()->fetchOne('
				SELECT storeIds
					FROM plenty_stack_item
					WHERE itemId = '. $itemId .'
			');

			// Add the store id
			$storeIds = explode('|', $stackedItem['storeIds']);
			if (!in_array($storeId, $storeIds))
			{
				$storeIds[] = $storeId;
				Shopware()->Db()->exec('
					UPDATE plenty_stack_item
						SET storeIds = "'. join('|', $storeIds) .'"
							WHERE itemId = '. $itemId .'
				');
			}
		}
	}

	/**
	 * Updates the stack
	 */
	public function update()
	{
		PlentymarketsLogger::getInstance()->message('Sync:Stack:Item', 'Starting update');

		$ShopRepository = Shopware()->Models()->getRepository('Shopware\Models\Shop\Shop');
		$Shops = $ShopRepository->findBy(
			array('active' => 1),
			array('default' => 'DESC')
		);

		// Remember the time
		$timestamp = time();

		// Is this the first run?
		$firstBlood = (integer) PlentymarketsConfig::getInstance()->getImportItemStackFirstRunTimestamp() == 0;

		$Request_GetItemsUpdated = new PlentySoapRequest_GetItemsUpdated();
		$Request_GetItemsUpdated->LastUpdateFrom = (integer) PlentymarketsConfig::getInstance()->getImportItemStackLastUpdateTimestamp();

		// Cache to avaid duplicate inserts of the same id with multiple shops
		$itemIdsStacked = array();

		foreach ($Shops as $Shop)
		{
			$Shop instanceof Shopware\Models\Shop\Shop;

			$Request_GetItemsUpdated->Page = 0;
			$Request_GetItemsUpdated->StoreID = PlentymarketsMappingController::getShopByShopwareID($Shop->getId());

			do {

				// Do the request
				$Response_GetItemsUpdated = PlentymarketsSoapClient::getInstance()->GetItemsUpdated($Request_GetItemsUpdated);

				foreach ((array) $Response_GetItemsUpdated->Items->item as $Object_Integer)
				{
					$itemId = $Object_Integer->intValue;

					// Skip existing items on the first run
					if ($Request_GetItemsUpdated->LastUpdateFrom == 0 && $firstBlood)
					{
						try
						{
							PlentymarketsMappingController::getItemByPlentyID($itemId);
							continue;
						}
						catch (PlentymarketsMappingExceptionNotExistant $E)
						{
						}
					}

					$this->addItem($itemId, $Request_GetItemsUpdated->StoreID);
					$itemIdsStacked[$itemId] = true;
				}
			}

			// Until all pages are received
			while (++$Request_GetItemsUpdated->Page < $Response_GetItemsUpdated->Pages);
		}

		// Upcomming last update timestamp
		PlentymarketsConfig::getInstance()->setImportItemStackLastUpdateTimestamp($timestamp);

		if ($firstBlood)
		{
			// Remember your very first time :)
			PlentymarketsConfig::getInstance()->setImportItemStackFirstRunTimestamp(time());
		}

		// Log
		$numberOfItemsStacked = count($itemIdsStacked);
		if (!$numberOfItemsStacked)
		{
			PlentymarketsLogger::getInstance()->message('Sync:Stack:Item', 'No item has been added to the stack');
		}
		else if ($numberOfItemsStacked == 1)
		{
			PlentymarketsLogger::getInstance()->message('Sync:Stack:Item', '1 item has been added to the stack');
		}
		else
		{
			PlentymarketsLogger::getInstance()->message('Sync:Stack:Item', count($itemIdsStacked) . ' items have been added to the stack');
		}
		PlentymarketsLogger::getInstance()->message('Sync:Stack:Item', 'Update finished');
	}

	/**
	 * Returns a chunk of item ids
	 *
	 * @param integer $limit
	 * @return array
	 */
	public function getChunk($limit)
	{
		// Start a transaction
		Shopware()->Db()->beginTransaction();

		// Select the "first in" items
		$items = Shopware()->Db()->fetchAll('
			SELECT itemId, storeIds
				FROM plenty_stack_item
				ORDER BY `timestamp` ASC, itemId ASC
				LIMIT '. (integer) $limit .'
		');

		// Delete 'em
		Shopware()->Db()->exec('
			DELETE FROM plenty_stack_item
				ORDER BY `timestamp` ASC, itemId ASC
				LIMIT '. (integer) $limit .'
		');

		// Commit the transaction
		Shopware()->Db()->commit();

		return $items;
	}

	/**
	 * Returns the number of items within the stack
	 *
	 * @see Countable::count()
	 */
	public function count()
	{
		return Shopware()->Db()->query('
			SELECT COUNT(*) FROM plenty_stack_item
		')->fetchColumn(0);
	}

}
