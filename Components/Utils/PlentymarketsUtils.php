<?php
require_once __DIR__ . '/../Config/PlentymarketsConfig.php';
require_once __DIR__ . '/../Mapping/PlentymarketsMappingController.php';
require_once __DIR__ . '/../Soap/Client/PlentymarketsSoapClient.php';
require_once __DIR__ . '/../Export/PlentymarketsExportController.php';

/**
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
	 *
	 * @param integer $shopwareID
	 * @return string
	 */
	public static function getExternalItemID($shopwareID)
	{
		return sprintf(self::EXTERNAL_ITEM_ID_FORMAT, (integer) $shopwareID);
	}

	/**
	 *
	 * @param integer $shopwareID
	 * @return string
	 */
	public static function getExternalCustomerID($shopwareID)
	{
		return sprintf(self::EXTERNAL_CUSTOMER_ID_FORMAT, (integer) $shopwareID);
	}

	/**
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
		if (PlentymarketsConfig::getInstance()->getApiWsdl('') == '')
		{
			PlentymarketsConfig::getInstance()->setPlentymarketsVersion('–');
			PlentymarketsConfig::getInstance()->setApiLastStatusTimestamp(time());
			PlentymarketsConfig::getInstance()->setApiStatus(1);
			return false;
		}

		try
		{
			$Response = PlentymarketsSoapClient::getInstance()->GetPlentyMarketsVersion();
			PlentymarketsConfig::getInstance()->setPlentymarketsVersion($Response->PlentyVersion);
			PlentymarketsConfig::getInstance()->setApiLastStatusTimestamp(time());
			PlentymarketsConfig::getInstance()->setApiStatus(2);

			return true;
		}
		catch (Exception $E)
		{
			PlentymarketsConfig::getInstance()->setPlentymarketsVersion('–');
			PlentymarketsConfig::getInstance()->setApiLastStatusTimestamp(time());
			PlentymarketsConfig::getInstance()->setApiStatus(1);

			return false;
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
		$exportStatus = PlentymarketsExportController::getInstance()->isSuccessFul();

		// Check whether all settings are set properly
		$settingsStatus = (
			!is_null($Config->getOutgoingItemsIntervalID()) &&
			!is_null($Config->getItemWarehouseID()) &&
			!is_null($Config->getItemProducerID()) &&
			!is_null($Config->getOrderReferrerID()) &&
			!is_null($Config->getOrderPaidStatusID()) &&
			!is_null($Config->getOutgoingItemsID()) &&
			!is_null($Config->getOutgoingItemsShopwareOrderStatusID()) &&
			!is_null($Config->getWebstoreID())
		);

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
}
