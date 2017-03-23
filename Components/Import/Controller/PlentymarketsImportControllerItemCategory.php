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
 * Imports the item categories.
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsImportControllerItemCategory
{
    /**
     * Performs the actual import.
     *
     * @param int $lastUpdateTimestamp
     */
    public function run($lastUpdateTimestamp)
    {
        $Request_GetItemCategoryCatalog = new PlentySoapRequest_GetItemCategoryCatalog();
        $Request_GetItemCategoryCatalog->Lang = 'de';
        $Request_GetItemCategoryCatalog->LastUpdateFrom = $lastUpdateTimestamp;

        $Request_GetItemCategoryCatalog->Page = 0;

        do {
            /** @var PlentySoapResponse_GetItemCategoryCatalog $Response_GetItemCategoryCatalog */
            $Response_GetItemCategoryCatalog = PlentymarketsSoapClient::getInstance()->GetItemCategoryCatalog($Request_GetItemCategoryCatalog);

            foreach ($Response_GetItemCategoryCatalog->Categories->item as $Category) {
                $PlentymarketsImportEntityItemCategory = new PlentymarketsImportEntityItemCategory($Category);
                $PlentymarketsImportEntityItemCategory->import();
            }
        } while (++$Request_GetItemCategoryCatalog->Page < $Response_GetItemCategoryCatalog->Pages);

        $importControllerItemCategoryTree = new PlentymarketsImportControllerItemCategoryTree();
        $importControllerItemCategoryTree->run();

        /** @var \Shopware\Components\Model\CategoryDenormalization $component */
        $component = Shopware()->CategoryDenormalization();
        $component->rebuildCategoryPath();
        $component->removeAllAssignments();
        $component->rebuildAllAssignments();
    }
}
