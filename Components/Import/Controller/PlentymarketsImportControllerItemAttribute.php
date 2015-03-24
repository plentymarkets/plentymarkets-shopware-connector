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


/**
 * Imports the item attributes
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsImportControllerItemAttribute
{
	/**
	 * Performs the actual import
	 *
	 * @param integer $lastUpdateTimestamp
	 */
	public function run($lastUpdateTimestamp)
	{

		$Request_GetItemAttributes = new PlentySoapRequest_GetItemAttributes();
		$Request_GetItemAttributes->GetValues = true;
		$Request_GetItemAttributes->LastUpdateFrom = $lastUpdateTimestamp;

		/** @var PlentySoapResponse_GetItemAttributes $Response_GetItemAttributes */
		$Response_GetItemAttributes = PlentymarketsSoapClient::getInstance()->GetItemAttributes($Request_GetItemAttributes);

		if (!$Response_GetItemAttributes->Success) {
			return;
		}

		foreach ($Response_GetItemAttributes->Attributes->item as $Attribute) 
		{
			$PlentymarketsImportEntityItemAttribute = new PlentymarketsImportEntityItemAttribute($Attribute);
			$PlentymarketsImportEntityItemAttribute->import();
		}

		// run import of attributes and attributes value translations
		$mainShops = PlentymarketsUtils::getShopwareMainShops();

		/** @var $mainShop Shopware\Models\Shop\Shop */
		foreach ($mainShops as $mainShop) 
		{
			// get all active languages of the main shop
			$activeLanguages = PlentymarketsTranslation::getShopActiveLanguages($mainShop->getId());

			foreach ($activeLanguages as $key => $language)
			{
				$Request_GetItemAttributes = new PlentySoapRequest_GetItemAttributes();
				$Request_GetItemAttributes->GetValues = true;
				$Request_GetItemAttributes->LastUpdateFrom = $lastUpdateTimestamp;
				$Request_GetItemAttributes->Lang = PlentymarketsTranslation::getPlentyLocaleFormat($language['locale']);

				/** @var PlentySoapResponse_GetItemAttributes $Response_GetItemAttributes */
				$Response_GetItemAttributes = PlentymarketsSoapClient::getInstance()->GetItemAttributes($Request_GetItemAttributes);

				if ($Response_GetItemAttributes->Success) 
				{
					foreach ($Response_GetItemAttributes->Attributes->item as $Attribute) 
					{
						$PlentymarketsImportEntityItemAttribute = new PlentymarketsImportEntityItemAttribute($Attribute);
						
						// set the atrribute translations from plenty for the language shops 
						if (!is_null($language['mainShopId']))
						{
							$languageShopID = PlentymarketsTranslation::getLanguageShopID($key, $language['mainShopId']);
							$PlentymarketsImportEntityItemAttribute->importTranslation($languageShopID);
						}
						else
						{
							// import translations for the main shop languages
							$PlentymarketsImportEntityItemAttribute->importTranslation($mainShop->getId());

						}
					}
				}
			}		
		}
	}
}

