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
 * Checks for items with non-existant main details
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsDataIntegrityCheckItemDetailPriceless implements PlentymarketsDataIntegrityCheckInterface
{
    /**
     * Returns the name of the check
     *
     * @see PlentymarketsDataIntegrityCheckInterface::getName()
     */
    public function getName()
    {
        return 'ItemDetailPriceless';
    }

    /**
     * Checks whether the check is valid
     *
     * @return bool
     */
    public function isValid()
    {
        return true; // count($this->getInvalidData(0, 1)) == 0;
    }

    /**
     * Returns a page of invalid data
     *
     * @param int $start
     * @param int $offset
     *
     * @return array
     */
    public function getInvalidData($start, $offset)
    {
        return Shopware()->Db()->query('
			SELECT
					SQL_CALC_FOUND_ROWS articleID itemId, COUNT(*) quantity, GROUP_CONCAT(ordernumber) ordernumber,
					GROUP_CONCAT(id) detailIds
					FROM s_articles_details
					WHERE id NOT IN (SELECT articledetailsID FROM s_articles_prices)
				GROUP BY articleID
				ORDER BY ordernumber, articleID
				LIMIT ' . $start . ', ' . $offset . '
		')->fetchAll();
    }

    /**
     * Deletes a page of invalid data
     *
     * @param int $start
     * @param int $offset
     */
    public function deleteInvalidData($start, $offset)
    {
        // Controller
        $controller = new PlentymarketsImportControllerItem();

        // StoreIds
        $stores = Shopware()->Db()->fetchAll('
			SELECT plentyID FROM plenty_mapping_shop
		');

        // Customer group
        $customerGroupKey = PlentymarketsConfig::getInstance()->getDefaultCustomerGroupKey();
        $customerGroupRepository = Shopware()->Models()->getRepository('Shopware\Models\Customer\Group');
        $customerGroups = $customerGroupRepository->findBy(['key' => $customerGroupKey]);
        $customerGroup = array_pop($customerGroups);

        foreach ($this->getInvalidData($start, $offset) as $data) {
            PyLog()->message('Fix:Item:Price', 'Start of fixing corrupt prices of the item id ' . $data['itemId']);

            // Search
            foreach (explode(',', $data['detailIds']) as $detailId) {
                try {
                    /** @var \Shopware\Models\Article\Detail $Detail */
                    $Detail = Shopware()->Models()->find('\Shopware\Models\Article\Detail', $detailId);

                    $price = new Shopware\Models\Article\Price();
                    $price->setFrom(1);
                    $price->setPrice(1);
                    $price->setPercent(0);
                    $price->setArticle($Detail->getArticle());
                    $price->setDetail($Detail);
                    $price->setCustomerGroup($customerGroup);

                    Shopware()->Models()->persist($price);
                } catch (Exception $E) {
                    PyLog()->debug($E->getMessage());
                }
            }

            Shopware()->Models()->flush();

            PyLog()->message('Fix:Item:Price', 'Finished with the fixing corrupt prices of the item id »' . $data['itemId'] . '«');

            try {
                foreach ($stores as $store) {
                    // Update the complete item from plenty
                    $controller->importItem(
                        PlentymarketsMappingController::getItemByShopwareID($data['itemId']), $store['plentyID']
                    );
                }
            } catch (Exception $e) {
                PyLog()->error('Fix:Item:Price', $e->getMessage());
            }

            // Stop after the first
            break;
        }
    }

    /**
     * Returns the fields to build an ext js model
     *
     * @return array
     */
    public function getFields()
    {
        return [
            [
                'name' => 'itemId',
                'description' => 'Artikel ID',
                'type' => 'int',
            ],
            [
                'name' => 'quantity',
                'description' => 'Anzahl',
                'type' => 'int',
            ],
            [
                'name' => 'ordernumber',
                'description' => 'Nummer(n)',
                'type' => 'string',
            ],
        ];
    }

    /**
     * Returns the total number of records
     *
     * @return int
     */
    public function getTotal()
    {
        return (int) Shopware()->Db()->query('
			SELECT FOUND_ROWS()
		')->fetchColumn(0);
    }
}
