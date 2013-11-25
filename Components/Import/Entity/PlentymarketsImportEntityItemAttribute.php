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
 * Imports an item attribute
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsImportEntityItemAttribute
{
	/**
	 *
	 * @var PlentySoapObject_GetItemAttributesAttribute
	 */
	protected $Attribute;

	/**
	 *
	 * @var Shopware\Models\Article\Configurator\Group
	 */
	protected $Group;

	/**
	 * I am the constructor
	 *
	 * @param PlentySoapObject_GetItemAttributesAttribute $Attribute
	 */
	public function __construct($Attribute)
	{
		$this->Attribute = $Attribute;
	}

	/**
	 * Persists the Attribute
	 */
	public function __destruct()
	{
		if ($this->Group)
		{
			Shopware()->Models()->persist($this->Group);
			Shopware()->Models()->flush();
		}
	}

	/**
	 * Imports the attribute and the values
	 */
	public function import()
	{
		$this->importAttribute();
		$this->importValues();
	}

	/**
	 * Imports the attribtue
	 */
	protected function importAttribute()
	{
		try
		{
			$SHOPWARE_attributeId = PlentymarketsMappingController::getAttributeGroupByPlentyID($this->Attribute->Id);
			PyLog()->message('Sync:Item:Attribute', 'Updating the attribute »' . $this->Attribute->FrontendName . '«');
		}
		catch (PlentymarketsMappingExceptionNotExistant $E)
		{
			PyLog()->message('Sync:Item:Attribute', 'Skipping the attribute »' . $this->Attribute->FrontendName . '«');
			return;
		}

		$Group = Shopware()->Models()->find('Shopware\Models\Article\Configurator\Group', $SHOPWARE_attributeId);
		$Group instanceof Shopware\Models\Article\Configurator\Group;

		// Set the new data
		$Group->setName($this->Attribute->FrontendName);
		$Group->setPosition($this->Attribute->Position);

		$this->Group = $Group;
	}

	/**
	 * Imports the values
	 */
	protected function importValues()
	{
		if (!$this->Group)
		{
			return;
		}

		foreach ($this->Attribute->Values->item as $Value)
		{
			$Value instanceof PlentySoapObject_GetItemAttributesAttributeValue;

			try
			{
				$SHOPWARE_optionId = PlentymarketsMappingController::getAttributeOptionByPlentyID($Value->ValueId);
			}
			catch (PlentymarketsMappingExceptionNotExistant $E)
			{
				return;
			}

			foreach ($this->Group->getOptions() as $Option)
			{
				$Option instanceof Shopware\Models\Article\Configurator\Option;
				if ($Option->getId() != $SHOPWARE_optionId)
				{
					continue;
				}

				$Option->setName($Value->FrontendName);
				$Option->setPosition($Value->Position);
			}
		}
	}
}
