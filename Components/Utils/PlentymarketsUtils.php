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

require_once PY_SOAP . 'Client/PlentymarketsSoapClient.php';
require_once PY_COMPONENTS . 'Config/PlentymarketsConfig.php';
require_once PY_COMPONENTS . 'Mapping/PlentymarketsMappingController.php';
require_once PY_COMPONENTS . 'Export/PlentymarketsExportController.php';

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
	 * Checks whether a connection can be made with the plentymarkets SOAP interface.
	 *
	 * @return boolean
	 */
	public static function checkApiConnectionStatus()
	{
		if (!PlentymarketsConfig::getInstance()->getApiWsdl())
		{
			PlentymarketsConfig::getInstance()->erasePlentymarketsVersion();
			PlentymarketsConfig::getInstance()->setApiLastStatusTimestamp(time());
			PlentymarketsConfig::getInstance()->setApiStatus(1);

			return false;
		}

		try
		{
			$Response = PlentymarketsSoapClient::getInstance()->GetServerTime();
			
			// 
			PlentymarketsConfig::getInstance()->setApiTimestampDeviation(time() - $Response->Timestamp);
			PlentymarketsConfig::getInstance()->setApiLastStatusTimestamp(time());
			PlentymarketsConfig::getInstance()->setApiStatus(2);
			
			// plenty version
			self::checkPlentymarketsVersion();

			return true;
		}
		catch (Exception $E)
		{
			PlentymarketsConfig::getInstance()->setApiTimestampDeviation(0);
			PlentymarketsConfig::getInstance()->setApiLastStatusTimestamp(time());
			PlentymarketsConfig::getInstance()->setApiStatus(1);

			return false;
		}
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
	 * Checks whether the mapping and initial export are complete and whether the settings were saved.
	 *
	 * @return boolean
	 */
	public static function checkMappingAndExportStatus()
	{
		$Config = PlentymarketsConfig::getInstance();

		$mappingStatus = PlentymarketsMappingController::isComplete();
		$exportStatus = PlentymarketsExportController::getInstance()->isComplete();
		$settingsStatus = $Config->isComplete();

		//
		$mayDatex = $mappingStatus && $exportStatus && $settingsStatus;

		// Den Status für den Datenaustausch ggf. deaktivieren
		if (!$mayDatex)
		{
			// Deaktivieren, wenn er aktiv ist
			// User bleibt bestehen
			$Config->setMayDatex(0);
			$Config->setMayDatexActual(0);
		}

		// Aktivieren, sofern der User das wünscht (Display)
		else
		{
			$Config->setMayDatex(1);
			if ($Config->getMayDatexUser(0))
			{
				$Config->setMayDatexActual(1);
			}
			else
			{
				$Config->setMayDatexActual(0);
			}
		}

		$Config->setIsSettingsFinished((integer) $settingsStatus);
		$Config->setIsExportFinished((integer) $exportStatus);
		$Config->setIsMappingFinished((integer) $mappingStatus);

		return $mayDatex;
	}

	/**
	 * Checks the data exchange status
	 *
	 * @see checkMappingAndExportStatus()
	 * @see checkApiConnectionStatus
	 * @return boolean
	 */
	public static function checkDxStatus()
	{
		return self::checkApiConnectionStatus() && self::checkMappingAndExportStatus();
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
}
