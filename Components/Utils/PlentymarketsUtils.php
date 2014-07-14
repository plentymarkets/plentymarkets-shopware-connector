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
 * The class PlentymarketsUtils contains different useful methods. The get-methods of this class are used
 * in some export and import entity classes. And the check-methods are used in the controllers PlentymarketsCronjobController
 * and Shopware_Controllers_Backend_Plentymarkets.
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsUtils
{

	/**
	 *
	 * @var string
	 */
	const EXTERNAL_ITEM_ID_FORMAT = 'Swag/%u';

	/**
	 *
	 * @var string
	 */
	const EXTERNAL_CUSTOMER_ID_FORMAT = 'Swag/%u';

	/**
	 *
	 * @var string
	 */
	const EXTERNAL_ORDER_ID_FORMAT = 'Swag/%u';

	/**
	 * Generates an external item id
	 *
	 * @param integer $shopwareID
	 * @return string
	 */
	public static function getExternalItemID($shopwareID)
	{
		return sprintf(self::EXTERNAL_ITEM_ID_FORMAT, (integer) $shopwareID);
	}

	/**
	 * Generates an external customer id
	 *
	 * @param integer $shopwareID
	 * @return string
	 */
	public static function getExternalCustomerID($shopwareID)
	{
		return sprintf(self::EXTERNAL_CUSTOMER_ID_FORMAT, (integer) $shopwareID);
	}

	/**
	 * Returns a shopware id from an external plentymarkets id
	 *
	 * @param string $externalItemID
	 * @return integer
	 */
	public static function getShopwareIDFromExternalItemID($externalItemID)
	{
		list ($shopwareID) = sscanf($externalItemID, self::EXTERNAL_ITEM_ID_FORMAT);

		return (integer) $shopwareID;
	}

	/**
	 * Returns a shopware id from an external plentymarkets id
	 *
	 * @param string $externalItemID
	 * @return integer
	 */
	public static function getShopwareIDFromExternalOrderID($externalItemID)
	{
		list ($shopwareID) = sscanf($externalItemID, self::EXTERNAL_ORDER_ID_FORMAT);

		return (integer) $shopwareID;
	}

	/**
	 * Retrieves the plentymarkets version
	 */
	public static function checkPlentymarketsVersion()
	{
		$timestamp = PlentymarketsConfig::getInstance()->getPlentymarketsVersionTimestamp(0);
		if ($timestamp < strtotime('- 12 hours'))
		{
			$Response = PlentymarketsSoapClient::getInstance()->GetPlentymarketsVersion();
			PlentymarketsConfig::getInstance()->setPlentymarketsVersion($Response->PlentyVersion);
			PlentymarketsConfig::getInstance()->setPlentymarketsVersionTimestamp(time());
		}
	}

	/**
	 * Checks whether two arrays are equal
	 *
	 * @param array $array1
	 * @param array $array2
	 * @return boolean
	 */
	public static function arraysAreEqual(array $array1, array $array2)
	{
		return count(array_diff($array1, $array2)) === 0;
	}

	/**
	 * Checks whether it's after midnight
	 *
	 * @return boolean
	 */
	public static function isAfterMidnight()
	{
		if (Shopware()->Bootstrap()->issetResource('License'))
		{
			$License = Shopware()->License();

			return $License->checkCoreLicense(false);
		}
		else
		{
			return false;
		}
	}

	/**
	 * Returns a human readable size
	 *
	 * @param integer $size
	 * @return string
	 */
	public static function convertBytes($size)
	{
		$unit = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');

		return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
	}

	/**
	 *
	 * @var array
	 */
	protected static $categoryId2ShopId = array();

	/**
	 * Returns an array with shop id for the given category id
	 *
	 * @param integer $categoryId
	 * @return array
	 */
	public static function getShopIdByCategoryRootId($categoryId)
	{
		if (!isset(self::$categoryId2ShopId[$categoryId]))
		{
			$shopIds = array();
			$shops = Shopware()->Db()->fetchAll('SELECT id FROM s_core_shops WHERE category_id = ' . $categoryId);
			foreach ($shops as $shop)
			{
				$shopIds[] = $shop['id'];
			}
			self::$categoryId2ShopId[$categoryId] = $shopIds;
		}

		return self::$categoryId2ShopId[$categoryId];
	}

	/**
	 * Registers the bundle custom modules
	 *
	 * @throws Exception
	 */
	public static function registerBundleModules()
	{
		$plugin = Shopware()->Db()->fetchRow('
			SELECT
					source, namespace, id
				FROM s_core_plugins
				WHERE name = "SwagBundle"
		');

		if (!$plugin)
		{
			throw new Exception('SwagBundle is not installed');
		}

		$path = realpath(
			Shopware()->AppPath() . '/Plugins/' . $plugin['source'] . '/' . $plugin['namespace'] . '/SwagBundle/Models/'
		);

		if (!$path)
		{
			throw new Exception('SwagBundle is not installed properly');
		}

		$path .= DIRECTORY_SEPARATOR;

		Shopware()->Loader()->registerNamespace(
			'Shopware\CustomModels', $path,
			Enlight_Loader::DEFAULT_SEPARATOR,
			Enlight_Loader::DEFAULT_EXTENSION,
			Enlight_Loader::POSITION_PREPEND
		);

		//$Loader = Shopware()->Loader();
		//echo 1;
	}

	/**
	 * Returns the root category id
	 *
	 * @param \Shopware\Models\Category\Category $category
	 * @return integer
	 */
	public static function getRootIdByCategory(Shopware\Models\Category\Category $category)
	{
		while ($category->getParentId())
		{
			$parent = $category->getParent();
			if ($parent->getLevel() == 0)
			{
				break;
			}
		}

		return $category->getId();
	}

	/**
	 * @var null|array
	 */
	protected static $availability = null;

	/**
	 * Returns the shipping time
	 *
	 * @param $availabilityId
	 * @return integer|null
	 */
	public static function getShippingTimeByAvailabilityId($availabilityId)
	{
		if ((integer) $availabilityId <= 0)
		{
			return null;
		}
		if (!is_array(self::$availability))
		{
			self::$availability = PlentymarketsImportController::getItemAvailability();
		}

		return isset(self::$availability[$availabilityId]) ? self::$availability[$availabilityId] : null;
	}
}
