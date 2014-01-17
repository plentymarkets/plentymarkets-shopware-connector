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

require_once PY_SOAP . 'Models/PlentySoapObject/GetLinkedItems.php';
require_once PY_SOAP . 'Models/PlentySoapRequest/GetLinkedItems.php';
require_once PY_COMPONENTS . 'Import/Entity/PlentymarketsImportEntityItemLinked.php';

/**
 * The class PlentymarketsImportController does the actual import for different cronjobs e.g.
 * in the class PlentymarketsCronjobController.
 * It uses the different import entities in /Import/Entity respectively in /Import/Entity/Order, for example PlentymarketsImportEntityItem.
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsImportControllerItemLinked
{

	/**
	 *
	 * @var PlentymarketsImportControllerItemLinked
	 */
	protected static $Instance;

	/**
	 *
	 * @var integer[]
	 */
	protected $itemIds = array();

	/**
	 * I am the constructor
	 */
	protected function __construct()
	{
	}

	/**
	 * Singleton: returns an instance
	 * 
	 * @return PlentymarketsImportControllerItemLinked
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
	 * Add an item id to the stack
	 * 
	 * @param integer $itemId        	
	 */
	public function addItem($itemId)
	{
		$this->itemIds[] = $itemId;
	}

	/**
	 * Imports the linked items
	 *
	 */
	public function run()
	{
		foreach (array_chunk($this->itemIds, 100) as $chunk)
		{
			$Request_GetLinkedItems = new PlentySoapRequest_GetLinkedItems();
			$Request_GetLinkedItems->ItemsList = array();
			
			//
			foreach ($chunk as $itemId)
			{
				$Object_GetLinkedItems = new PlentySoapObject_GetLinkedItems();
				$Object_GetLinkedItems->ItemID = $itemId;
				$Request_GetLinkedItems->ItemsList[] = $Object_GetLinkedItems;
			}
			
			/** @var PlentySoapResponse_GetLinkedItems $Response_GetLinkedItems */
			$Response_GetLinkedItems = PlentymarketsSoapClient::getInstance()->GetLinkedItems($Request_GetLinkedItems);

			/** @var PlentySoapResponseObject_GetLinkedItems $Item */
			foreach ($Response_GetLinkedItems->Items->item as $Item)
			{
				try
				{
					$SHOPWARE_itemId = PlentymarketsMappingController::getItemByPlentyID($Item->ItemID);
				}
				catch (PlentymarketsMappingExceptionNotExistant $E)
				{
					continue;
				}
				
				$PlentymarketsImportEntityItemLinked = new PlentymarketsImportEntityItemLinked($SHOPWARE_itemId, $Item->LinkedItems);
				$PlentymarketsImportEntityItemLinked->link();
			}
		}
	}
}
