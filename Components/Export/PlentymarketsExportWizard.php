<?php

require_once PY_COMPONENTS . 'Export/PlentymarketsExportController.php';
require_once PY_COMPONENTS . 'Export/Status/PlentymarketsExportStatusController.php';

class PlentymarketsExportWizard
{
	/**
	 * 
	 * @var PlentymarketsExportStatusController
	 */
	protected $StatusController;
	
	protected static $Instance;
	
	protected function __construct()
	{
		$this->StatusController = PlentymarketsExportStatusController::getInstance();
	}
	
	public static function getInstance()
	{
		if (!self::$Instance instanceof self)
		{
			self::$Instance = new self();
		}
		return self::$Instance;
	}
	
	public function conjure()
	{
		//
		if (!$this->isActive())
		{
			return;
		}
		
		// If the export is blocked (an entity is pending or running)
		// there is nothing to do for the wizzard
		if (!$this->StatusController->mayAnnounce() || $this->StatusController->isFinished())
		{
			// Deactivate
			$this->deactivate();
			return;
		}
		
		try
		{
			// Get the next entity
			$EntityStatus = $this->StatusController->getNext();
			
			// and announce it
			PlentymarketsExportController::getInstance()->announce($EntityStatus->getName());
			
			// Log
			PlentymarketsLogger::getInstance()->message('Export:Initial:Wizard', 'Automatically announced '. $EntityStatus->getName());
		}
		catch (PlentymarketsExportException $E)
		{
			PlentymarketsLogger::getInstance()->error('Export:Initial:Wizard', $E->getMessage());
			$this->deactivate();
		}
		catch (PlentymarketsExportStatusException $E)
		{
			PlentymarketsLogger::getInstance()->error('Export:Initial:Wizard', 'No entity to announce');
			$this->deactivate();
		}
	}
	
	public function isActive()
	{
		return (boolean) PlentymarketsConfig::getInstance()->getIsExportWizardActive();
	}
	
	public function mayActivate()
	{
		return $this->StatusController->mayAnnounce() && !$this->StatusController->isFinished();
	}
	
	public function activate()
	{
		PlentymarketsConfig::getInstance()->setIsExportWizardActive(1);
	}
	
	public function deactivate()
	{
		PlentymarketsConfig::getInstance()->setIsExportWizardActive(0);
	}
}
