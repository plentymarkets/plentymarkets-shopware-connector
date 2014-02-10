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
class PlentymarketsDataIntegrityCheckItemVariationOptionNotInSet implements PlentymarketsDataIntegrityCheckInterface
{
	/**
	 * Returns the name of the check
	 *
	 * @see PlentymarketsDataIntegrityCheckInterface::getName()
	 */
	public function getName()
	{
		return 'ItemVariationOptionNotInSet';
	}

	/**
	 * Checks whether the check is valid
	 *
	 * @return boolean
	 */
	public function isValid()
	{
		return count($this->getInvalidData(0, 1)) == 0;
	}

	/**
	 * Returns a page of invalid data
	 *
	 * @param integer $start
	 * @param integer $offset
	 * @return array
	 */
	public function getInvalidData($start, $offset)
	{
		return Shopware()->Db()->query('
				SELECT SQL_CALC_FOUND_ROWS sa.name, sad.ordernumber, sa.id itemId, article_id detailsId, GROUP_CONCAT(saco.name SEPARATOR "|") `options`, GROUP_CONCAT(sacg.name SEPARATOR "|") groups
				FROM s_article_configurator_option_relations sacor
				JOIN s_articles_details sad ON sad.id = sacor.article_id
				LEFT JOIN s_articles sa ON sa.id = sad.articleID
				LEFT JOIN s_article_configurator_sets ascs ON ascs.id = sa.configurator_set_id
				LEFT JOIN s_article_configurator_set_option_relations sacsor ON sacsor.set_id = ascs.id AND sacsor.option_id = sacor.option_id
				LEFT JOIN s_article_configurator_options saco ON saco.id = sacor.option_id
				LEFT JOIN s_article_configurator_groups sacg ON sacg.id = saco.group_id
				WHERE sacsor.option_id IS NULL
				GROUP BY article_id
				ORDER BY sad.ordernumber DESC, article_id
				LIMIT ' . $start . ', ' . $offset . '
		')->fetchAll();
	}

	/**
	 * Deletes a page of invalid data
	 *
	 * @param integer $start
	 * @param integer $offset
	 */
	public function deleteInvalidData($start, $offset)
	{
		foreach ($this->getInvalidData($start, $offset) as $data)
		{
			try
			{
				$Item = Shopware()->Models()->find('\Shopware\Models\Article\Detail', $data['detailsId']);
				Shopware()->Models()->remove($Item);
			}
			catch (Exception $E)
			{
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
		return array(
			array(
				'name' => 'name',
				'description' => 'Bezeichnung',
				'type' => 'string'
			),
			array(
				'name' => 'ordernumber',
				'description' => 'Nummer',
				'type' => 'string'
			),
			array(
				'name' => 'itemId',
				'description' => 'Artikel ID',
				'type' => 'int'
			),
			array(
				'name' => 'detailsId',
				'description' => 'Detail ID',
				'type' => 'int'
			),
			array(
				'name' => 'options',
				'description' => 'Optionen',
				'type' => 'string'
			),
			array(
				'name' => 'groups',
				'description' => 'Gruppe',
				'type' => 'string'
			)
		);
	}

	/**
	 * Returns the total number of records
	 *
	 * @return integer
	 */
	public function getTotal()
	{
		return (integer) Shopware()->Db()->query('
			SELECT FOUND_ROWS()
		')->fetchColumn(0);
	}
}
