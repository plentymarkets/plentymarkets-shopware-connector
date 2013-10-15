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

require_once PY_COMPONENTS . 'Export/PlentymarketsExportController.php';
require_once PY_COMPONENTS . 'Export/Status/PlentymarketsExportStatusController.php';

/**
 * Export wizard - automatically announces the next export
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsExportWizard
{

	/**
	 *
	 * @var PlentymarketsExportStatusController
	 */
	protected $StatusController;

	/**
	 *
	 * @var PlentymarketsExportWizard
	 */
	protected static $Instance;

	/**
	 * Contructor method
	 */
	protected function __construct()
	{
		$this->StatusController = PlentymarketsExportStatusController::getInstance();
	}

	/**
	 * Singleton method
	 *
	 * @return PlentymarketsExportWizard
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
	 * Does the actual magic :)
	 */
	public function conjure()
	{
		//
		if (!$this->isActive())
		{
			return;
		}

		// If there is nothing to do for the wizzard
		if ($this->StatusController->isBroke())
		{
			// Deactivate
			$this->deactivate();

			// Log
			PlentymarketsLogger::getInstance()->message('Export:Initial:Wizard', 'Automatically disabled because an entity is broke');

			return;
		}

		// If there is nothing to do for the wizzard
		if ($this->StatusController->isFinished())
		{
			// Deactivate
			$this->deactivate();

			// Log
			PlentymarketsLogger::getInstance()->message('Export:Initial:Wizard', 'Automatically disabled because there is nothing more to do');

			return;
		}

		// Entity is already waiting (item chunks)
		if ($this->StatusController->isWaiting())
		{
			// Log
			PlentymarketsLogger::getInstance()->message('Export:Initial:Wizard', 'An entity is already waiting');

			return;
		}

		try
		{
			// Get the next entity
			$EntityStatus = $this->StatusController->getNext();

			// and announce it
			PlentymarketsExportController::getInstance()->announce($EntityStatus->getName());

			// Log
			PlentymarketsLogger::getInstance()->message('Export:Initial:Wizard', 'Automatically announced ' . $EntityStatus->getName());
		}
		catch (PlentymarketsExportException $E)
		{
			PlentymarketsLogger::getInstance()->error('Export:Initial:Wizard', $E->getMessage(), $E->getCode());
			$this->deactivate();
		}
		catch (PlentymarketsExportStatusException $E)
		{
			PlentymarketsLogger::getInstance()->error('Export:Initial:Wizard', $E->getMessage(), $E->getCode());
			$this->deactivate();
		}
	}

	/**
	 * Checkes wheter the wizard is active
	 *
	 * @return boolean
	 */
	public function isActive()
	{
		return (boolean) PlentymarketsConfig::getInstance()->getIsExportWizardActive();
	}

	/**
	 * Checks whether the wizard may be activated
	 *
	 * @return boolean
	 */
	public function mayActivate()
	{
		return $this->StatusController->mayAnnounce() && !$this->StatusController->isFinished();
	}

	/**
	 * Activates the wizard
	 */
	public function activate()
	{
		// Active
		PlentymarketsConfig::getInstance()->setIsExportWizardActive(1);

		// Kickstart
		$this->conjure();
	}

	/**
	 * Deactivates the wizard
	 */
	public function deactivate()
	{
		PlentymarketsConfig::getInstance()->setIsExportWizardActive(0);
	}
}
