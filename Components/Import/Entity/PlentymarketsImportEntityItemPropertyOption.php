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
 * Imports a property option
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsImportEntityItemPropertyOption
{

	/**
	 *
	 * @var PlentySoapObject_Property
	 */
	protected $Option;

	/**
	 * I am the contructor
	 *
	 * @param PlentySoapObject_Property $Group
	 */
	public function __construct($Option)
	{
		$this->Option = $Option;
	}

	/**
	 * Does the actual import
	 */
	public function import()
	{
		try
		{
			$SHOPWARE_id = PlentymarketsMappingController::getPropertyByPlentyID($this->Option->PropertyID);
			PyLog()->message('Sync:Item:Property:Option', 'Updating the property option »' . $this->Option->PropertyFrontendName . '«');
		}
		catch (PlentymarketsMappingExceptionNotExistant $E)
		{
			PyLog()->message('Sync:Item:Property:Option', 'Skipping the property option »' . $this->Option->PropertyFrontendName . '«');
			return;
		}

		$propertyParts = explode(';', $SHOPWARE_id);
		$optionId = $propertyParts[1];

		/** @var Shopware\Models\Property\Option $Option */
		$Option = Shopware()->Models()->find('Shopware\Models\Property\Option', $optionId);

		// Set the new data
		$Option->setName($this->Option->PropertyFrontendName);
		Shopware()->Models()->persist($Option);
		Shopware()->Models()->flush();
	}
}
