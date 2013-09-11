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
 * Helperfuncttion for the item sync.
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsImportItemHelper
{

	/**
	 *
	 * @var integer
	 */
	protected static $numbersCreated = 0;
	
	/**
	 * Checks whether the item numer is existant
	 *
	 * @param integer $number
	 * @return boolean
	 */
	public static function itemNumberExists($number)
	{
		$detail = Shopware()->Models()
		->getRepository('Shopware\Models\Article\Detail')
		->findOneBy(array(
			'number' => $number
		));
	
		return !empty($detail);
	}
	
	/**
	 * Returns a generated item number
	 *
	 * @return string
	 */
	public static function getItemNumber()
	{
		$prefix = Shopware()->Config()->backendAutoOrderNumberPrefix;
	
		$sql = "SELECT number FROM s_order_number WHERE name = 'articleordernumber'";
		$number = Shopware()->Db()->fetchOne($sql);
		$number += self::$numbersCreated;
	
		do
		{
			++$number;
			++self::$numbersCreated;
	
			$sql = "SELECT id FROM s_articles_details WHERE ordernumber LIKE ?";
			$hit = Shopware()->Db()->fetchOne($sql, $prefix . $number);
		}
		while ($hit);
	
		Shopware()->Db()->query("UPDATE s_order_number SET number = ? WHERE name = 'articleordernumber'", array(
		$number
		));
	
		return $prefix . $number;
	}
	
	/**
	 * Returns a usable item number
	 *
	 * @param string $number
	 * @return string
	 */
	public static function getUsableNumber($number)
	{
		if (!empty($number) && !self::itemNumberExists($number))
		{
			return $number;
		}
		return self::getItemNumber();
	}
}
