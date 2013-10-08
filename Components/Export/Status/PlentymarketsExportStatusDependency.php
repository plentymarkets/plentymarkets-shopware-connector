<?php
require_once PY_COMPONENTS . 'Export/Status/PlentymarketsExportStatus.php';

class PlentymarketsExportStatusDependency extends PlentymarketsExportStatus
{

	/**
	 *
	 * @var PlentymarketsExportStatusInterface
	 */
	protected $Dependency;

	/**
	 *
	 * @see PlentymarketsExportStatusInterface::mayAnnounce()
	 */
	public function mayAnnounce()
	{
		return $this->getStatus() == 'open' && !$this->needsDependency();
	}

	/**
	 *
	 * @see PlentymarketsExportStatusInterface::needsDependency()
	 */
	public function needsDependency()
	{
		return $this->Dependency->needsDependency() || !$this->Dependency->isFinished();
	}

	/**
	 *
	 * @return PlentymarketsExportStatusInterface
	 */
	public function getDependency()
	{
		return $this->Dependency;
	}

	/**
	 *
	 * @param PlentymarketsExportStatusInterface $Dependency        	
	 */
	public function setDependency(PlentymarketsExportStatusInterface $Dependency)
	{
		$this->Dependency = $Dependency;
	}
}
