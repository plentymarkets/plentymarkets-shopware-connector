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


/**
 * PlentymarketsExportEntityItemLinked provides the actual items export functionality. Like the other export
 * entities this class is called in PlentymarketsExportController. It is important to deliver the correct
 * article model to the constructor method of this class, which is \Shopware\Models\Article\Article.
 * The data export takes place based on plentymarkets SOAP-calls.
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsExportEntityItemLinked
{

	/**
	 *
	 * @var Shopware\Models\Article\Article
	 */
	protected $SHOPWARE_Article;

	/**
	 * Constructor method
	 *
	 * @param Shopware\Models\Article\Article $Article
	 */
	public function __construct(Shopware\Models\Article\Article $Article)
	{
		$this->SHOPWARE_Article = $Article;
	}

	/**
	 * Links the items (similar/accessory)
	 */
	public function link()
	{
		$Request_AddLinkedItems = new PlentySoapRequest_AddLinkedItems();
		$Request_AddLinkedItems->CrosssellingList = array();

		foreach ($this->SHOPWARE_Article->getSimilar() as $Similar)
		{
			$Object_AddLinkedItems = new PlentySoapObject_AddLinkedItems();
			$Object_AddLinkedItems->Relationship = 'Similar'; // string
			$Object_AddLinkedItems->CrossItemSKU = PlentymarketsMappingController::getItemByShopwareID($Similar->getId()); // string
			$Request_AddLinkedItems->CrosssellingList[] = $Object_AddLinkedItems;
		}

		foreach ($this->SHOPWARE_Article->getRelated() as $Related)
		{
			$Object_AddLinkedItems = new PlentySoapObject_AddLinkedItems();
			$Object_AddLinkedItems->Relationship = 'Accessory'; // string
			$Object_AddLinkedItems->CrossItemSKU = PlentymarketsMappingController::getItemByShopwareID($Related->getId());
			$Request_AddLinkedItems->CrosssellingList[] = $Object_AddLinkedItems;
		}

		if (!count($Request_AddLinkedItems->CrosssellingList))
		{
			return;
		}

		$Request_AddLinkedItems->MainItemSKU = PlentymarketsMappingController::getItemByShopwareID($this->SHOPWARE_Article->getId()); // string

		// Do the request
		PlentymarketsSoapClient::getInstance()->AddLinkedItems($Request_AddLinkedItems);
	}
}
