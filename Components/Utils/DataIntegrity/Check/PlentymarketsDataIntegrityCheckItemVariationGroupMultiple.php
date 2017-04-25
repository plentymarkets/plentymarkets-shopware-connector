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
 * Find item variantions with multiple attribute options per group
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsDataIntegrityCheckItemVariationGroupMultiple implements PlentymarketsDataIntegrityCheckInterface
{
    /**
     * Returns the name of the check
     *
     * @see PlentymarketsDataIntegrityCheckInterface::getName()
     */
    public function getName()
    {
        return 'ItemVariationGroupMultiple';
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
        return Shopware()->Db()->query('
			SELECT SQL_CALC_FOUND_ROWS a.name, ad.ordernumber, article_id detailsId, count(*) - 1 diff, co.group_id groupId, a.id itemId, GROUP_CONCAT(cor.option_id SEPARATOR "|") optionIds, GROUP_CONCAT(co.name SEPARATOR ", ") `option`, cg.name `group`
				FROM s_article_configurator_option_relations  cor
				LEFT JOIN s_article_configurator_options co ON co.id = cor.option_id
				LEFT JOIN s_article_configurator_groups cg ON cg.id = co.group_id
				LEFT JOIN s_articles_details ad ON ad.id = cor.article_id
				LEFT JOIN s_articles a ON a.id = ad.articleID
				GROUP BY article_id, co.group_id
				HAVING count(*) > 1
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
            try {
                $Item = Shopware()->Models()->find('\Shopware\Models\Article\Detail', $data['detailsId']);
                Shopware()->Models()->remove($Item);
            } catch (Exception $E) {
                PlentymarketsLogger::getInstance()->error(__LINE__ . __METHOD__, $E->getMessage());
                foreach (explode('|', $data['optionIds']) as $optionId) {
                    Shopware()->Db()->query('
						DELETE FROM s_article_configurator_option_relations
							WHERE
								article_id = ? AND
								option_id = ?
							LIMIT 1
					', [
                        $data['detailsId'],
                        $optionId,
                    ]);
                }
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
                'description' => 'Bezeichnung',
                'type' => 'string',
            ],
            [
                'name' => 'ordernumber',
                'description' => 'Nummer',
                'type' => 'string',
            ],
            [
                'name' => 'itemId',
                'description' => 'Artikel ID',
                'type' => 'int',
            ],
            [
                'name' => 'detailsId',
                'description' => 'Detail ID',
                'type' => 'int',
            ],
            [
                'name' => 'option',
                'description' => 'Optionen',
                'type' => 'string',
            ],
            [
                'name' => 'group',
                'description' => 'Gruppe',
                'type' => 'string',
            ],
            [
                'name' => 'groupId',
                'description' => 'Gruppen ID',
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
