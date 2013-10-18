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

require_once PY_COMPONENTS . 'Export/PlentymarketsExportWizard.php';
require_once PY_COMPONENTS . 'Export/Status/PlentymarketsExportStatus.php';
require_once PY_COMPONENTS . 'Export/Status/PlentymarketsExportStatusDependency.php';

/**
 * Controlles the initial export status
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsExportStatusController
{
	/**
	 *
	 * @var PlentymarketsExportStatusController
	 */
	protected static $Instance;

	/**
	 *
	 * @var array[PlentymarketsExportStatus]
	 */
	protected $Status = array();

	/**
	 * I am the contructor
	 */
	protected function __construct()
	{
		// ItemCategory
		$ItemCategory = new PlentymarketsExportStatus('ItemCategory');
		$this->add($ItemCategory);

		// ItemAttribute
		$ItemAttribute = new PlentymarketsExportStatusDependency('ItemAttribute');
		$ItemAttribute->setDependency($ItemCategory);
		$this->add($ItemAttribute);

		// ItemProperty
		$ItemProperty = new PlentymarketsExportStatusDependency('ItemProperty');
		$ItemProperty->setDependency($ItemAttribute);
		$this->add($ItemProperty);

		// ItemProducer
		$ItemProducer = new PlentymarketsExportStatusDependency('ItemProducer');
		$ItemProducer->setDependency($ItemProperty);
		$this->add($ItemProducer);

		// Item
		$Item = new PlentymarketsExportStatusDependency('Item');
		$Item->setDependency($ItemProducer);
		$this->add($Item);

		// ItemCrossSelling
		$ItemCrossSelling = new PlentymarketsExportStatusDependency('ItemCrossSelling');
		$ItemCrossSelling->setDependency($Item);
		$this->add($ItemCrossSelling);

		// Customer
		$Customer = new PlentymarketsExportStatus('Customer');
		$Customer->setOptional();
		$this->add($Customer);
	}

	/**
	 * I am the singleton method
	 *
	 * @return PlentymarketsExportStatusController
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
	 * Adds a status to the controller
	 *
	 * @param PlentymarketsExportStatus $Status
	 */
	public function add(PlentymarketsExportStatus $Status)
	{
		$this->Status[$Status->getName()] = $Status;
	}

	/**
	 * Returns the overview for the use in the view
	 *
	 * @return array
	 */
	public function getOverview()
	{
		$wizardIsActive = PlentymarketsExportWizard::getInstance()->isActive();
		$overview = array();

		foreach ($this->Status as $position => $Status)
		{
			$Status instanceof PlentymarketsExportStatus;

			$overview[$Status->getName()] = array(
				// Position
				'position' => $position,

				// Data
				'name' => $Status->getName(),
				'status' => $Status->getStatus(),
				'error' => htmlspecialchars($Status->getError()),
				'start' => $Status->getStart(),
				'finished' => $Status->getFinished(),

				// Flags
				'mayAnnounce' => !$wizardIsActive && $this->mayAnnounce() && $Status->mayAnnounce(),
				'mayReset' => !$wizardIsActive && $Status->mayReset(),
				'mayErase' => !$wizardIsActive && $Status->mayErase(),
				'isOverdue' => $Status->isOverdue(),
				'needsDependency' => !$wizardIsActive && $Status->needsDependency() && !$Status->isFinished()
			);
		}

		return $overview;
	}

	/**
	 * Returns an entity
	 *
	 * @param string $entity
	 * @return PlentymarketsExportStatus
	 */
	public function getEntity($entity)
	{
		return $this->Status[$entity];
	}

	/**
	 * Returns the next entity to be announced
	 *
	 * @throws PlentymarketsExportStatusException if there is no announceable entity
	 * @return PlentymarketsExportStatus
	 */
	public function getNext()
	{
		foreach ($this->Status as $Status)
		{
			if ($Status->mayAnnounce())
			{
				return $Status;
			}
		}
		throw new PlentymarketsExportStatusException('No entity to announce', 2010);
	}

	/**
	 * Checks whether an entity may be announced
	 *
	 * @return boolean
	 */
	public function mayAnnounce()
	{
		if ($this->isBroke())
		{
			return false;
		}
		foreach ($this->Status as $Status)
		{
			if ($Status->isBlocking())
			{
				return false;
			}
		}
		return true;
	}

	/**
	 * Checks whether the specifies entity may be announced
	 *
	 * @param string $entity
	 * @return boolean
	 */
	public function mayAnnounceEntity($entity)
	{
		return $this->mayAnnounce() && $this->Status[$entity]->mayAnnounce();
	}

	/**
	 * Checks whether the whole export is blocked through an broke entity
	 *
	 * @return boolean
	 */
	public function isBroke()
	{
		foreach ($this->Status as $Status)
		{
			if ($Status->isBroke())
			{
				return true;
			}
		}
		return false;
	}

	/**
	 * Checks wheter on of the exports is waiting to be carried out
	 *
	 * @return boolean
	 */
	public function isWaiting()
	{
		foreach ($this->Status as $Status)
		{
			if ($Status->isWaiting())
			{
				return true;
			}
		}
		return false;
	}

	/**
	 * Checks wheter the mandatory exports are finished
	 *
	 * @return boolean
	 */
	public function isFinished()
	{
		foreach ($this->Status as $Status)
		{
			if (!$Status->isOptional() && !$Status->isFinished())
			{
				return false;
			}
		}
		return true;
	}
}
