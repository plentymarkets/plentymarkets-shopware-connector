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
 * @copyright  Copyright (c) 2013, plentymarkets GmbH (http://www.plentymarkets.com)
 * @author     Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */

/**
 * Find item details with non-existant items.
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsDataIntegrityCheckItemOrphaned implements PlentymarketsDataIntegrityCheckInterface
{
    /**
     * Returns the name of the check.
     *
     * @see PlentymarketsDataIntegrityCheckInterface::getName()
     */
    public function getName()
    {
        return 'ItemOrphaned';
    }

    /**
     * Checks whether the check is valid.
     *
     * @return bool
     */
    public function isValid()
    {
        return count($this->getInvalidData(0, 1)) == 0;
    }

    /**
     * Returns a page of invalid data.
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
					SQL_CALC_FOUND_ROWS id detailId, articleID itemId, ordernumber, additionaltext
					FROM s_articles_details
					WHERE articleID NOT IN (SELECT id FROM s_articles)
				ORDER BY ordernumber, articleID
				LIMIT '.$start.', '.$offset.'
		')->fetchAll();
    }

    /**
     * Deletes a page of invalid data.
     *
     * @param int $start
     * @param int $offset
     */
    public function deleteInvalidData($start, $offset)
    {
        foreach ($this->getInvalidData($start, $offset) as $data) {
            try {
                $Item = Shopware()->Models()->find('\Shopware\Models\Article\Detail', $data['detailId']);
                Shopware()->Models()->remove($Item);
            } catch (Exception $E) {
            }
        }
        Shopware()->Models()->flush();
    }

    /**
     * Returns the fields to build an ext js model.
     *
     * @return array
     */
    public function getFields()
    {
        return [
            [
                'name'        => 'itemId',
                'description' => 'Artikel ID',
                'type'        => 'int',
            ],
            [
                'name'        => 'detailId',
                'description' => 'Detail ID',
                'type'        => 'int',
            ],
            [
                'name'        => 'ordernumber',
                'description' => 'Nummer',
                'type'        => 'string',
            ],
            [
                'name'        => 'additionaltext',
                'description' => 'Zusatztext',
                'type'        => 'string',
            ],
        ];
    }

    /**
     * Returns the total number of records.
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
