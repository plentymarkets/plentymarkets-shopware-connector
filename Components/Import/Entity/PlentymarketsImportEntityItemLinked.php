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

/**
 * PlentymarketsImportEntityItemLinked provides the actual linked items import functionality.
 * Like the other import entities this class is called in PlentymarketsImportController.
 * It is important to deliver at least a plenty item ID or
 * a shopware item ID to the constructor method of this class.
 * The data import takes place based on plentymarkets SOAP-calls.
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsImportEntityItemLinked
{

	/**
	 *
	 * @var integer
	 */
	protected $SHOPWARE_itemId;

	/**
	 *
	 * @var PlentySoapObject_GetLinkedItems
	 */
	protected $LinkedItems;

	/**
	 * I am the constructor
	 *
	 * @param integer $itemId
	 * @param PlentySoapObject_GetLinkedItems $GetLinkedItems
	 */
	public function __construct($itemId, $GetLinkedItems)
	{
		$this->SHOPWARE_itemId = $itemId;
		$this->LinkedItems = $GetLinkedItems;
	}

	/**
	 * Deletes all linkes items
	 */
	protected function purge()
	{
		Shopware()->Db()->delete('s_articles_relationships', 'articleID = ' . (integer) $this->SHOPWARE_itemId);
		Shopware()->Db()->delete('s_articles_similar', 'articleID = ' . (integer) $this->SHOPWARE_itemId);
	}

	/**
	 * Retrieves the linked items from plentymarkets and links them
	 */
	public function link()
	{
		// Cleanup
		$this->purge();

		foreach ($this->LinkedItems->item as $LinkedItem)
		{
			$LinkedItem instanceof PlentySoapObject_GetLinkedItems;

			// Get the id
			try
			{
				$SHOWWARE_linkedItemId = PlentymarketsMappingController::getItemByPlentyID($LinkedItem->ItemID);
			}
			catch (PlentymarketsMappingExceptionNotExistant $E)
			{
				continue;
			}

			if ($LinkedItem->Relationship == 'Accessory')
			{
				$table = 's_articles_relationships';
			}
			else if ($LinkedItem->Relationship == 'Similar')
			{
				$table = 's_articles_similar';
			}
			else
			{
				continue;
			}

			Shopware()->Db()->insert(
				$table,
				array(
					'articleID' => (integer) $this->SHOPWARE_itemId,
					'relatedarticle' => (integer) $SHOWWARE_linkedItemId
				)
			);
		}
	}
}
