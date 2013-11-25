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
 * Imports an item category
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsImportEntityItemCategory
{
	/**
	 *
	 * @var PlentySoapObject_GetItemCategoryCatalogBase
	 */
	protected $Category;

	/**
	 *
	 * @param PlentySoapObject_GetItemCategoryCatalogBase $Category
	 */
	public function __construct($Category)
	{
		$this->Category = $Category;
	}

	/**
	 * Does the actual import
	 */
	public function import()
	{
		$match = Shopware()->Db()->fetchRow('
			SELECT
					plentyPath,
					shopwareId
				FROM plenty_category
				WHERE plentyId = '. (integer) $this->Category->CategoryID .'
				ORDER BY size ASC
				LIMIT 1
		');

		// If there is not match, the categoty ain't used in shopware
		if (!$match)
		{
			return PyLog()->message('Sync:Item:Attribute', 'Skipping the category »' . $this->Category->Name . '« (unused)');
		}

		// Helper
		$path = explode(';', $match['plentyPath']);
		$hit = false;

		// Get the corresponding shopware leaf
		$Category = Shopware()->Models()->find('Shopware\Models\Category\Category', $match['shopwareId']);
		$Category instanceof Shopware\Models\Category\Category;

		// If the shopware categoty wasn't found, something is terribly wrong
		if (!$Category)
		{
			return PyLog()->message('Sync:Item:Attribute', 'Skipping the category »' . $this->Category->Name . '« (not found)');
		}

		// Walk through the plentymarkets path until the right one is found
		while ($path)
		{
			if (array_pop($path) == $this->Category->CategoryID)
			{
				$hit = true;
				break;
			}

			// If this one is not the correct on, get the next higher category
			$Category = $Category->getParent();
		}

		// If no shopware categoty was found, again something is terribly wrong
		if (!$hit)
		{
			return PyLog()->message('Sync:Item:Attribute', 'Skipping the category »' . $this->Category->Name . '« (none found)');
		}

		// Update the category only if the name's changed
		if ($Category->getName() != $this->Category->Name)
		{
			PyLog()->message('Sync:Item:Attribute', 'Updating the category »' . $this->Category->Name . '«');
			$Category->setName($this->Category->Name);

			Shopware()->Models()->persist($Category);
			Shopware()->Models()->flush();
		}
	}
}
