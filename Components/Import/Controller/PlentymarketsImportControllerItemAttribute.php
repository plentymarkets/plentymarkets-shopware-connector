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

require_once PY_SOAP . 'Models/PlentySoapRequest/GetItemAttributes.php';
require_once PY_COMPONENTS . 'Import/Entity/PlentymarketsImportEntityItemAttribute.php';

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

		if (!$Response_GetItemAttributes->Success)
		{
			return;
		}

		foreach ($Response_GetItemAttributes->Attributes->item as $Attribute)
		{
			$PlentymarketsImportEntityItemAttribute = new PlentymarketsImportEntityItemAttribute($Attribute);
			$PlentymarketsImportEntityItemAttribute->import();
		}
	}
}

