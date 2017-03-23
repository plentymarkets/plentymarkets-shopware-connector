<?php
/**
 * plentymarkets shopware connector
 * Copyright © 2013 plentymarkets GmbH.
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
 * Imports an item category.
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsImportEntityItemCategory
{
    /**
     * @var PlentySoapObject_GetItemCategoryCatalog
     */
    protected $Category;

    /**
     * I am the constructor.
     *
     * @param PlentySoapObject_GetItemCategoryCatalog $Category
     */
    public function __construct($Category)
    {
        $this->Category = $Category;
    }

    /**
     * Does the actual import.
     */
    public function import()
    {
        $row = Shopware()->Db()->fetchAll('
			SELECT *
				FROM plenty_mapping_category
				WHERE plentyID LIKE "'.$this->Category->CategoryID.';%"
				LIMIT 1
		');

        if (!$row) {
            // PyLog()->message('Sync:Item:Category', 'Skipping the category »' . $this->Category->Name . '« (not found)');
            return;
        }

        $index = explode(PlentymarketsMappingEntityCategory::DELIMITER, $row[0]['shopwareID']);
        $categoryId = $index[0];

        /** @var Shopware\Models\Category\Category $Category */
        $Category = Shopware()->Models()->find('Shopware\Models\Category\Category', $categoryId);

        // If the shopware category wasn't found, something is terribly wrong
        if (!$Category) {
            PyLog()->message('Sync:Item:Category', 'Skipping the category »'.$this->Category->Name.'« (not found)');

            return;
        }

        // Update the category only if the name's changed
        if ($Category->getName() != $this->Category->Name || $Category->getPosition() != $this->Category->Position) {
            PyLog()->message('Sync:Item:Category', 'Updating the category »'.$this->Category->Name.'«');
            $Category->setName($this->Category->Name);
            $Category->setPosition($this->Category->Position);

            Shopware()->Models()->persist($Category);
            Shopware()->Models()->flush();
        }
    }
}
