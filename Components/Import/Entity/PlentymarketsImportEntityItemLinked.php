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

require_once __DIR__ . '/../../Soap/Models/PlentySoapObject/GetLinkedItems.php';
require_once __DIR__ . '/../../Soap/Models/PlentySoapRequest/GetLinkedItems.php';

/**
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsImportEntityItemLinked
{

	/**
	 *
	 * @var integer
	 */
	protected $PLENTY_itemId;

	/**
	 *
	 * @var integer
	 */
	protected $SHOPWARE_itemId;

	/**
	 *
	 * @param integer $PLENTY_itemId
	 * @param integer $SHOPWARE_itemId
	 */
	public function __construct($PLENTY_itemId, $SHOPWARE_itemId = null)
	{
		$this->PLENTY_itemId = $PLENTY_itemId;
		if (is_null($SHOPWARE_itemId))
		{
			$this->SHOPWARE_itemId = PlentymarketsMappingController::getItemByPlentyID($PLENTY_itemId);
		}
		else
		{
			$this->SHOPWARE_itemId = $SHOPWARE_itemId;
		}
	}

	/**
	 */
	public function purge()
	{
		Shopware()->Db()->query('DELETE FROM s_articles_relationships WHERE articleID = ' . (integer) $this->SHOPWARE_itemId);
		Shopware()->Db()->query('DELETE FROM s_articles_similar WHERE articleID = ' . (integer) $this->SHOPWARE_itemId);
	}

	/**
	 */
	public function link()
	{
		$Request_GetLinkedItems = new PlentySoapRequest_GetLinkedItems();
		$Request_GetLinkedItems->ItemsList = array();

		$Object_GetLinkedItems = new PlentySoapObject_GetLinkedItems();
		$Object_GetLinkedItems->ItemID = $this->PLENTY_itemId;
		$Request_GetLinkedItems->ItemsList[] = $Object_GetLinkedItems;

		// Do the request
		$Response_GetLinkedItems = PlentymarketsSoapClient::getInstance()->GetLinkedItems($Request_GetLinkedItems);
		$Response_GetLinkedItems instanceof PlentySoapResponse_GetLinkedItems;

		if ($Response_GetLinkedItems->Success == false)
		{
			PlentymarketsLogger::getInstance()->error('Sync:Item:Linked', 'Got negative success from GetLinkedItems for plentymarkets itemId '. $this->PLENTY_itemId);
			return;
		}

		// Cleanup
		$this->purge();

		foreach ($Response_GetLinkedItems->Items->item as $Items)
		{
			$Items instanceof PlentySoapResponseObject_GetLinkedItems;

			foreach ($Items->LinkedItems->item as $LinkedItem)
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

				Shopware()->Db()->query('
					INSERT INTO ' . $table . '
						SET
							articleID = ' . (integer) $this->SHOPWARE_itemId . ',
							relatedarticle = ' . (integer) $SHOWWARE_linkedItemId . '
				');
			}
		}
	}
}
