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
 * PlentymarketsImportEntityItemLinked provides the actual linked items import functionality.
 * Like the other import entities this class is called in PlentymarketsImportController.
 * It is important to deliver at least a plenty item ID or
 * a shopware item ID to the constructor method of this class.
 * The data import takes place based on plentymarkets SOAP-calls.
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsImportEntityItemLinked
{
    /**
     * @var int
     */
    protected $SHOPWARE_itemId;

    /**
     * @var PlentySoapObject_GetLinkedItems
     */
    protected $LinkedItems;

    /**
     * I am the constructor
     *
     * @param int $itemId
     * @param PlentySoapObject_GetLinkedItems $GetLinkedItems
     */
    public function __construct($itemId, $GetLinkedItems)
    {
        $this->SHOPWARE_itemId = (int) $itemId;
        $this->LinkedItems = $GetLinkedItems;
    }

    /**
     * Retrieves the linked items from plentymarkets and links them
     */
    public function link()
    {
        // Cleanup
        $this->purge();

        /** @var PlentySoapObject_GetLinkedItems $LinkedItem */
        foreach ($this->LinkedItems->item as $LinkedItem) {
            try {
                $SHOWWARE_linkedItemId = PlentymarketsMappingController::getItemByPlentyID($LinkedItem->ItemID);
            } catch (PlentymarketsMappingExceptionNotExistant $E) {
                continue;
            }

            $table = '';

            if ($LinkedItem->Relationship === 'Accessory') {
                $table = 's_articles_relationships';
            } elseif ($LinkedItem->Relationship === 'Similar') {
                $table = 's_articles_similar';
            } else {
                // Allow plugins to change the data
            $table = Enlight()->Events()->filter(
                'PlentyConnector_ImportEntityItemLinked_GetTable',
                $table,
                [
                    'subject' => $this,
                    'item' => $LinkedItem,
                ]
            );

                if (empty($table)) {
                    continue;
                }
            }

            Shopware()->Db()->insert(
            $table,
            [
                'articleID' => (int) $this->SHOPWARE_itemId,
                'relatedarticle' => (int) $SHOWWARE_linkedItemId,
            ]
        );
        }

        $this->purgeViews();
    }

    /**
     * Deletes all linked items
     */
    protected function purge()
    {
        Shopware()->Db()->delete('s_articles_relationships', 'articleID = ' . $this->SHOPWARE_itemId);
        Shopware()->Db()->delete('s_articles_similar', 'articleID = ' . $this->SHOPWARE_itemId);

        // Allow plugins to change the data
        Enlight()->Events()->notify(
            'PlentyConnector_ImportEntityItemLinked_AfterPurge',
            [
                'subject' => $this,
                'id' => $this->SHOPWARE_itemId,
            ]
        );
    }

    /**
     * Deletes the view counter for non existent links
     */
    protected function purgeViews()
    {
        Shopware()->Db()->exec('
			DELETE FROM
					s_articles_similar_shown_ro
				WHERE
					article_id = ' . $this->SHOPWARE_itemId . ' AND
					related_article_id NOT IN (
						SELECT relatedarticle FROM s_articles_similar WHERE articleID = ' . $this->SHOPWARE_itemId . '
					)
		');
    }
}
