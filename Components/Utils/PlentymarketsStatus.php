<?php

require_once PY_SOAP . 'Client/PlentymarketsSoapClient.php';
require_once PY_COMPONENTS . 'Config/PlentymarketsConfig.php';
require_once PY_COMPONENTS . 'Mapping/PlentymarketsMappingController.php';
require_once PY_COMPONENTS . 'Export/PlentymarketsExportController.php';
require_once PY_COMPONENTS . 'Utils/DataIntegrity/PlentymarketsDataIntegrityController.php';

class PlentymarketsStatus
{

	protected static $Instance;

	protected $isConnected = false;

	protected $connectionTimestamp = 0;

	public static function getInstance()
	{
		if (!self::$Instance instanceof self)
		{
			self::$Instance = new self();
		}
		return self::$Instance;
	}

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

	protected function isSettingsFinished()
	{
		$isSettingsFinished = PlentymarketsConfig::getInstance()->isComplete();
		PlentymarketsConfig::getInstance()->set('IsSettingsFinished', (integer) $isSettingsFinished);
		return $isSettingsFinished;
	}

	protected function isMappingFinished()
	{
		$isMappingFinished = PlentymarketsMappingController::isComplete();
		PlentymarketsConfig::getInstance()->set('IsMappingFinished', (integer) $isMappingFinished);
		return $isMappingFinished;
	}

	protected function isExportFinished()
	{
		$isExportFinished = PlentymarketsExportController::getInstance()->isComplete();
		PlentymarketsConfig::getInstance()->set('IsExportFinished', (integer) $isExportFinished);
		return $isExportFinished;
	}

	protected function isDataIntegrityValid()
	{
		$isDataIntegrityValid = PlentymarketsDataIntegrityController::getInstance()->isValid();;
		PlentymarketsConfig::getInstance()->set('IsDataIntegrityValid', (integer) $isDataIntegrityValid);
		return $isDataIntegrityValid;
	}

	public function mayImport()
	{
		return $this->isConnected();
	}

	public function mayExport()
	{
		return (
			// Connection is okay
			$this->isConnected() &&

			// Config is okay
			$this->isSettingsFinished() &&

			// Mapping is okay
			$this->isMappingFinished()
		);
	}

	public function maySynchronize()
	{
		// Export has basically the same needs
		$mayExport = $this->mayExport();

		// Export is okay
		$isExportFinished = $this->isExportFinished();

		// useless, so far, bit the integrity needs to be checked
		$this->isDataIntegrityValid();

		// May synchron
		$maySynchronize = $mayExport && $isExportFinished;

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

		return $maySynchronize && $mayDatexActual;
	}
}
