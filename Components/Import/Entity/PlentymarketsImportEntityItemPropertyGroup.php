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
 * Imports a property group
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsImportEntityItemPropertyGroup
{
	/**
	 *
	 * @var PlentySoapObject_PropertyGroup
	 */
	protected $Group;

	/**
	 * I am the constructor
	 *
	 * @param PlentySoapObject_PropertyGroup $Group
	 */
	public function __construct($Group)
	{
		$this->Group = $Group;
	}

	/**
	 * @param int $shopId
	 */
	public function importPropertyGroupTranslation($shopId)
	{
		try
		{
			$SHOPWARE_id = PlentymarketsMappingController::getPropertyGroupByPlentyID($this->Group->PropertyGroupID);
			PyLog()->message('Sync:Item:Producer', 'Updating the property group translation »' . $this->Group->FrontendName . '«');
		}
		catch (PlentymarketsMappingExceptionNotExistant $E)
		{
			PyLog()->message('Sync:Item:Producer', 'Skipping the property group translation »' . $this->Group->FrontendName . '«');
			return;
		}

		if(!is_null($this->Group->FrontendName))
		{
			// save the translation of the property group
			$properteryGroup_TranslationData = array('groupName' => $this->Group->FrontendName);

			PlentymarketsTranslation::setShopwareTranslation('propertygroup', $SHOPWARE_id, $shopId, $properteryGroup_TranslationData);
		}
	}

	/**
	 * Does the actual import
	 */
	public function import()
	{
		try
		{
			$SHOPWARE_id = PlentymarketsMappingController::getPropertyGroupByPlentyID($this->Group->PropertyGroupID);
			PyLog()->message('Sync:Item:Producer', 'Updating the property group »' . $this->Group->FrontendName . '«');
		}
		catch (PlentymarketsMappingExceptionNotExistant $E)
		{
			PyLog()->message('Sync:Item:Producer', 'Skipping the property group »' . $this->Group->FrontendName . '«');
			return;
		}

		/** @var Shopware\Models\Property\Group $Group */
		$Group = Shopware()->Models()->find('Shopware\Models\Property\Group', $SHOPWARE_id);

		// Set the new data
		$Group->setName($this->Group->FrontendName);
		Shopware()->Models()->persist($Group);
		Shopware()->Models()->flush();
	}
}
