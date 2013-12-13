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
require_once PY_COMPONENTS . 'Utils/DataIntegrity/PlentymarketsDataIntegrityController.php';

/**
 * Determines the status of the plugin regarding to the communication with plenty
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsStatus
{
	/**
	 *
	 * @var PlentymarketsStatus
	 */
	protected static $Instance;

	/**
	 *
	 * @var boolean
	 */
	protected $isConnected = false;

	/**
	 *
	 * @var integer
	 */
	protected $connectionTimestamp = 0;

	/**
	 *
	 * @var boolean
	 */
	protected $isCliWarningLogged = false;

	/**
	 *
	 * @var boolean
	 */
	protected $isRuntimeWarningLogged = false;

	/**
	 *
	 * @var boolean
	 */
	protected $isLicenseWarningLogged = false;

	/**
	 * I am the singleton method
	 *
	 * @return PlentymarketsStatus
	 */
	public static function getInstance()
	{
		if (!self::$Instance instanceof self)
		{
			self::$Instance = new self();
		}
		return self::$Instance;
	}

	/**
	 * Checks whether the connection to plentymarkets can be established
	 *
	 * @return boolean
	 */
	public function isConnected()
	{
		// The connection is only checked every 10 seconds
		if ($this->isConnected && $this->connectionTimestamp > time() - 10)
		{
			return true;
		}

		$this->isConnected = false;

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

			$this->isConnected = true;
			$this->connectionTimestamp = time();

			// plenty version
			PlentymarketsUtils::checkPlentymarketsVersion();

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
	 * Checks whether the settings are completely done
	 *
	 * @return boolean
	 */
	protected function isSettingsFinished()
	{
		$isSettingsFinished = PlentymarketsConfig::getInstance()->isComplete();
		PlentymarketsConfig::getInstance()->set('IsSettingsFinished', (integer) $isSettingsFinished);
		return $isSettingsFinished;
	}

	/**
	 * Checks whether the mappings are completely done
	 *
	 * @return boolean
	 */
	protected function isMappingFinished()
	{
		$isMappingFinished = PlentymarketsMappingController::isComplete();
		PlentymarketsConfig::getInstance()->set('IsMappingFinished', (integer) $isMappingFinished);
		return $isMappingFinished;
	}

	/**
	 * Checks whether the initial exports are completely done
	 *
	 * @return boolean
	 */
	protected function isExportFinished()
	{
		$isExportFinished = PlentymarketsExportController::getInstance()->isComplete();
		PlentymarketsConfig::getInstance()->set('IsExportFinished', (integer) $isExportFinished);
		return $isExportFinished;
	}

	/**
	 * Checks whether the data integerity is valid
	 *
	 * @return boolean
	 */
	protected function isDataIntegrityValid()
	{
		$isDataIntegrityValid = PlentymarketsDataIntegrityController::getInstance()->isValid();;
		PlentymarketsConfig::getInstance()->set('IsDataIntegrityValid', (integer) $isDataIntegrityValid);
		return $isDataIntegrityValid;
	}

	/**
	 * Checks whether data may be imported
	 *
	 * @return boolean
	 */
	public function mayImport()
	{
		return $this->isConnected();
	}

	/**
	 * Checks whether data may be exported
	 *
	 * @return boolean
	 */
	public function mayExport()
	{
		return (
			// Connection is okay
			$this->isConnected() &&

			// Config is okay
			$this->isSettingsFinished() &&

			// Mapping is okay
			$this->isMappingFinished()  &&

			// Data is fine
			$this->isDataIntegrityValid()
		);
	}

	/**
	 * Checks whether data may be synchronized
	 *
	 * @param boolean $checkExtended Perform the extended checks (memory and runtime)
	 * @return boolean
	 */
	public function maySynchronize($checkExtended=true)
	{
		// Export has basically the same needs
		$mayExport = $this->mayExport();

		// Export is okay
		$isExportFinished = $this->isExportFinished();

		// Check the license
		if (Shopware()->Bootstrap()->issetResource('License'))
		{
			$License = Shopware()->License();
			$isLicenseValid = $License->checkCoreLicense(false);
			if (!$isLicenseValid && !$this->isLicenseWarningLogged)
			{
				PlentymarketsLogger::getInstance()->error('System:License', 'The shopware license that is used is invalid or has expired. No synchronization will be performed.', 1010);
				$this->isLicenseWarningLogged = true;
			}
		}
		else
		{
			$isLicenseValid = false;
			if (!$this->isLicenseWarningLogged)
			{
				PlentymarketsLogger::getInstance()->error('System:License', 'The license mananger is not installed. Therefore, it is not possible to check the license. No synchronization will be performed.', 1011);
				$this->isLicenseWarningLogged = true;
			}
		}

		// May Synchronize
		$maySynchronize = $mayExport && $isExportFinished && $isLicenseValid;

		// User settings
		$mayDatexActual = PlentymarketsConfig::getInstance()->getMayDatexUser(0);

		//
		if (!$maySynchronize)
		{
			// Deactivate the sync and remember the setting
			PlentymarketsConfig::getInstance()->setMayDatex(0);
			PlentymarketsConfig::getInstance()->setMayDatexActual(0);
		}

		else
		{
			// Remember the setting and activate or deactive the sync
			// depending on the user's choice
			PlentymarketsConfig::getInstance()->setMayDatex(1);
			PlentymarketsConfig::getInstance()->setMayDatexActual($mayDatexActual);
		}

		// status vars
		$isCli = true;
		$mayRunUnlimited = true;

		// Skip the checks - if you know what you are doing :)
		$skipChecks = isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] == 'overruleExtendedChecks';

		// do some extended checks whether the sync may be started
		if ($checkExtended && !$skipChecks)
		{
			// Check the cli
			$sapi = php_sapi_name();
			if ($sapi != 'cli')
			{
				$isCli = false;
				if (!$this->isCliWarningLogged)
				{
					PlentymarketsLogger::getInstance()->error('System:PHP', 'The synchronizing processes have to be started with the PHP-CLI (command line interface). You are using »' . $sapi . '«.', 1001);
					if (isset($_ENV['_']))
					{
						PlentymarketsLogger::getInstance()->error('System:PHP', 'The prozess is handled through »' . $_ENV['_'] . '«.', 1001);
					}
					if (isset($_SERVER['HTTP_REFERER']))
					{
						PlentymarketsLogger::getInstance()->error('System:PHP', 'The prozess is called through »' . $_SERVER['HTTP_REFERER'] . '«.', 1001);
					}
					$this->isCliWarningLogged = true;
				}
			}

			// Check the runtime
			$runtime = ini_get('max_execution_time');
			if ($runtime > 0)
			{
				$mayRunUnlimited = false;
				if (!$this->isRuntimeWarningLogged)
				{
					PlentymarketsLogger::getInstance()->error('System:PHP', 'The synchronizing processes have to be started with unlimited runtime. Your runtime is limited to »' . $runtime . '« seconds.', 1002);
					$this->isRuntimeWarningLogged = true;
				}
			}
		}

		return $maySynchronize && $mayDatexActual && $isCli && $mayRunUnlimited;
	}
}
