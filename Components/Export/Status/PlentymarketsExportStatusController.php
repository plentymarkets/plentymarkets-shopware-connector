<?php

require_once PY_COMPONENTS . 'Export/Status/PlentymarketsExportStatusInterface.php';
require_once PY_COMPONENTS . 'Export/Status/PlentymarketsExportStatus.php';
require_once PY_COMPONENTS . 'Export/Status/PlentymarketsExportStatusDependency.php';

class PlentymarketsExportStatusController
{
	protected static $Instance;

	protected $Status = array();

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

	public static function getInstance()
	{
		if (!self::$Instance instanceof self)
		{
			self::$Instance = new self();
		}
		return self::$Instance;
	}

	public function add(PlentymarketsExportStatusInterface $Status)
	{
		$this->Status[$Status->getName()] = $Status;
	}

	public function getOverview()
	{
		$overview = array();

		foreach ($this->Status as $position => $Status)
		{
			$Status instanceof PlentymarketsExportStatusInterface;

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
				'mayAnnounce' => $this->mayAnnounce() && $Status->mayAnnounce(),
				'mayReset' => $Status->mayReset(),
				'mayErase' => $Status->mayErase(),
				'needsDependency' => $Status->needsDependency() && !$Status->isFinished()
			);
		}

		return $overview;
	}

	/**
	 * Returns the next entity to be announced
	 *
	 * @return PlentymarketsExportStatusInterface
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
		throw new PlentymarketsExportStatusException('No entity to announce');
	}

	public function mayAnnounce()
	{
		foreach ($this->Status as $Status)
		{
			if ($Status->isBlocking())
			{
				return false;
			}
		}
		return true;
	}

	public function mayAnnounceEntity($entity)
	{
		return $this->mayAnnounce() && $this->Status[$entity]->mayAnnounce();
	}

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
