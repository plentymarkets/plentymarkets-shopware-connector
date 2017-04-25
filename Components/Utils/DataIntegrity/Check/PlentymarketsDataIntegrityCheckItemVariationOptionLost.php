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
 * Find items with non-existant properties
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsDataIntegrityCheckItemVariationOptionLost implements PlentymarketsDataIntegrityCheckInterface
{
    /**
     * Returns the name of the check
     *
     * @see PlentymarketsDataIntegrityCheckInterface::getName()
     */
    public function getName()
    {
        return 'ItemVariationOptionLost';
    }

    /**
     * Checks whether the check is valid
     *
     * @return bool
     */
    public function isValid()
    {
        return count($this->getInvalidData(0, 1)) == 0;
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
        // SELECT article_id, option_id FROM s_article_configurator_option_relations  cor
        //WHERE option_id NOT IN (SELECT id FROM s_article_configurator_options);
        return Shopware()->Db()->query('
			SELECT
					SQL_CALC_FOUND_ROWS a.name, ad.ordernumber, ad.additionaltext, article_id detailsId, a.id itemId, option_id optionId
				FROM s_article_configurator_option_relations cor
				LEFT JOIN s_articles_details ad ON ad.id = cor.article_id
				LEFT JOIN s_articles a ON a.id = ad.articleID
				WHERE option_id NOT IN (SELECT id FROM s_article_configurator_options)
				ORDER BY ad.ordernumber DESC, article_id
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
        foreach ($this->getInvalidData($start, $offset) as $data) {
            // Item detail still available
            if (!empty($data['ordernumber'])) {
                try {
                    $Detail = Shopware()->Models()->find('\Shopware\Models\Article\Detail', $data['detailsId']);
                    Shopware()->Models()->remove($Detail);
                } catch (Exception $E) {
                }
            }

            // delete only the relation
            else {
                Shopware()->Db()->query('
					DELETE FROM s_article_configurator_option_relations
						WHERE
							article_id = ? AND
							option_id = ?
						LIMIT 1
				', [
                    $data['detailsId'],
                    $data['optionId'],
                ]);
            }
        }
        Shopware()->Models()->flush();
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
                'name' => 'name',
                'description' => 'Beueichnung',
                'type' => 'string',
            ],
            [
                'name' => 'ordernumber',
                'description' => 'Nummer',
                'type' => 'string',
            ],
            [
                'name' => 'additionaltext',
                'description' => 'Zusatztext',
                'type' => 'string',
            ],
            [
                'name' => 'detailsId',
                'description' => 'Detail ID',
                'type' => 'int',
            ],
            [
                'name' => 'itemId',
                'description' => 'Artikel ID',
                'type' => 'int',
            ],
            [
                'name' => 'optionId',
                'description' => 'Option ID',
                'type' => 'int',
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
