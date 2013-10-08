<?php

require_once PY_COMPONENTS . 'Export/Status/PlentymarketsExportStatusInterface.php';

class PlentymarketsExportStatus implements PlentymarketsExportStatusInterface
{
	const STATUS_OPEN = 'open';
	const STATUS_SUCCESS = 'success';
	const STATUS_PENDING = 'pending';
	const STATUS_ERROR = 'error';
	const STATUS_RUNNING = 'running';

	protected $name;

	protected $isOptional = false;

	public function __construct($name)
	{
		$this->name = $name;
	}
	
	/**
	 *
	 * @see PlentymarketsExportStatusInterface::isFinished()
	 */
	public function isFinished()
	{
		return $this->getStatus() == self::STATUS_SUCCESS;
	}

	/**
	 *
	 * @see PlentymarketsExportStatusInterface::mayAnnounce()
	 */
	public function mayAnnounce()
	{
		return $this->getStatus() == self::STATUS_OPEN;
	}

	/**
	 *
	 * @see PlentymarketsExportStatusInterface::mayErase()
	 */
	public function mayErase()
	{
		return $this->getStart() != -1;
	}

	/**
	 *
	 * @see PlentymarketsExportStatusInterface::mayReset()
	 */
	public function mayReset()
	{
		return $this->getStatus() != self::STATUS_OPEN;
	}

	/**
	 *
	 * @see PlentymarketsExportStatusInterface::needsDependency()
	 */
	public function needsDependency()
	{
		return false;
	}

	/**
	 */
	public function setOptional()
	{
		$this->isOptional = true;
	}

	/**
	 *
	 * @see PlentymarketsExportStatusInterface::isBlocking()
	 */
	public function isBlocking()
	{
		return $this->getStatus() == self::STATUS_PENDING || $this->getStatus() == self::STATUS_RUNNING;
	}

	/**
	 *
	 * @see PlentymarketsExportStatusInterface::isOptional()
	 */
	public function isOptional()
	{
		return $this->isOptional;
	}

	/**
	 *
	 * @return field_type
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 *
	 * @return field_type
	 */
	public function getStatus()
	{
		$method = sprintf('get%sExportStatus', $this->name);
		return PlentymarketsConfig::getInstance()->$method(self::STATUS_OPEN);
	}

	/**
	 *
	 * @return number
	 */
	public function getStart()
	{
		$method = sprintf('get%sExportTimestampStart', $this->name);
		return (integer) PlentymarketsConfig::getInstance()->$method(-1);
	}

	/**
	 *
	 * @return number
	 */
	public function getFinished()
	{
		$method = sprintf('get%sExportTimestampFinished', $this->name);
		return (integer) PlentymarketsConfig::getInstance()->$method(-1);
	}

	/**
	 *
	 * @return field_type
	 */
	public function getError()
	{
		$method = sprintf('get%sExportLastErrorMessage', $this->name);
		return (string) PlentymarketsConfig::getInstance()->$method();
	}

	/**
	 *
	 * @param field_type $name        	
	 */
	public function setName($name)
	{
		$this->name = $name;
	}

	/**
	 *
	 * @param field_type $status        	
	 */
	public function setStatus($status)
	{
		$method = sprintf('set%sExportStatus', $this->name);
		PlentymarketsConfig::getInstance()->$method($status);
	}

	/**
	 *
	 * @param number $start        	
	 */
	public function setStart($start)
	{
		$method = sprintf('set%sExportTimestampStart', $this->name);
		PlentymarketsConfig::getInstance()->$method($start);
	}

	/**
	 *
	 * @param number $finished        	
	 */
	public function setFinished($finished)
	{
		$method = sprintf('set%sExportTimestampFinished', $this->name);
		PlentymarketsConfig::getInstance()->$method($finished);
	}

	/**
	 *
	 * @param field_type $error        	
	 */
	public function setError($error)
	{
		$method = sprintf('set%sExportLastErrorMessage', $this->name);
		PlentymarketsConfig::getInstance()->$method($error);
	}
}
