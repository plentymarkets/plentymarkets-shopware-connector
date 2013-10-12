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

require_once PY_COMPONENTS . 'Export/Status/PlentymarketsExportStatusInterface.php';

/**
 * Represents the status of an initial export
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsExportStatus implements PlentymarketsExportStatusInterface
{

	/**
	 *
	 * @var string
	 */
	const STATUS_OPEN = 'open';

	/**
	 *
	 * @var string
	 */
	const STATUS_SUCCESS = 'success';

	/**
	 *
	 * @var string
	 */
	const STATUS_PENDING = 'pending';

	/**
	 *
	 * @var string
	 */
	const STATUS_ERROR = 'error';

	/**
	 *
	 * @var string
	 */
	const STATUS_RUNNING = 'running';

	/**
	 *
	 * @var integer
	 */
	const SECONDS_OVERDUE = 900;

	/**
	 *
	 * @var string
	 */
	protected $name;

	/**
	 *
	 * @var boolean
	 */
	protected $isOptional = false;

	/**
	 * I am the contructor
	 *
	 * @param string $name
	 */
	public function __construct($name)
	{
		$this->name = $name;
	}

	/**
	 * Checks whether the export is finished
	 *
	 * @return boolean
	 */
	public function isFinished()
	{
		return $this->getStatus() == self::STATUS_SUCCESS;
	}

	/**
	 * Checks whether the export may be announced
	 *
	 * @see PlentymarketsExportStatusInterface::mayAnnounce()
	 * @return boolean
	 */
	public function mayAnnounce()
	{
		return $this->getStatus() == self::STATUS_OPEN;
	}

	/**
	 * Checks whether the export may be erased
	 *
	 * @return boolean
	 */
	public function mayErase()
	{
		return $this->getStart() != -1;
	}

	/**
	 * Checks whether the export may be resetted
	 *
	 * @return boolean
	 */
	public function mayReset()
	{
		return $this->getStatus() != self::STATUS_OPEN;
	}

	/**
	 * Checks whether the export depends on another export
	 *
	 * @see PlentymarketsExportStatusInterface::needsDependency()
	 * @return boolean
	 */
	public function needsDependency()
	{
		return false;
	}

	/**
	 * Sets the export as optional
	 */
	public function setOptional()
	{
		$this->isOptional = true;
	}

	/**
	 * Checks whether the export is blocked
	 *
	 * @return boolean
	 */
	public function isBlocking()
	{
		return $this->getStatus() == self::STATUS_PENDING || $this->getStatus() == self::STATUS_RUNNING;
	}

	/**
	 * Checks whether the export is broken
	 *
	 * @return boolean
	 */
	public function isBroke()
	{
		return $this->getStatus() == self::STATUS_ERROR;
	}

	/**
	 * Checks whether the export is waiting
	 *
	 * @return boolean
	 */
	public function isWaiting()
	{
		return $this->getStatus() == self::STATUS_PENDING;
	}

	/**
	 * Checks whether the export is optional
	 *
	 * @return boolean
	 */
	public function isOptional()
	{
		return $this->isOptional;
	}

	/**
	 * Checks whether the next call is overdue
	 *
	 * @return boolean
	 */
	public function isOverdue()
	{
		if ($this->getStatus() != self::STATUS_RUNNING)
		{
			return false;
		}

		$lastCallTimestamp = (integer) PlentymarketsConfig::getInstance()->getInitialExportLastCallTimestamp();

		if ($lastCallTimestamp == 0)
		{
			return false;
		}

		return $lastCallTimestamp < (time() - self::SECONDS_OVERDUE);
	}

	/**
	 * Returns the name of the export
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Returns the status of the export
	 *
	 * @see PlentymarketsExportStatusInterface::getStatus()
	 * @return string
	 */
	public function getStatus()
	{
		$method = sprintf('get%sExportStatus', $this->name);
		return PlentymarketsConfig::getInstance()->$method(self::STATUS_OPEN);
	}

	/**
	 * Returns the start timestmap
	 *
	 * @see PlentymarketsExportStatusInterface::getStart()
	 * @return integer
	 */
	public function getStart()
	{
		$method = sprintf('get%sExportTimestampStart', $this->name);
		return (integer) PlentymarketsConfig::getInstance()->$method(-1);
	}

	/**
	 * Returns the finshed timestamp
	 *
	 * @see PlentymarketsExportStatusInterface::getFinished()
	 * @return integer
	 */
	public function getFinished()
	{
		$method = sprintf('get%sExportTimestampFinished', $this->name);
		return (integer) PlentymarketsConfig::getInstance()->$method(-1);
	}

	/**
	 * Returns the error message
	 *
	 * @see PlentymarketsExportStatusInterface::getError()
	 * @return string
	 */
	public function getError()
	{
		$method = sprintf('get%sExportLastErrorMessage', $this->name);
		return (string) PlentymarketsConfig::getInstance()->$method();
	}

	/**
	 * Sets the name of the export
	 *
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = $name;
	}

	/**
	 * Sets the status of the export
	 *
	 * @param string $status
	 */
	public function setStatus($status)
	{
		$method = sprintf('set%sExportStatus', $this->name);
		PlentymarketsConfig::getInstance()->$method($status);
	}

	/**
	 * Sets the start timestmamp
	 *
	 * @param integer $start
	 */
	public function setStart($start)
	{
		$method = sprintf('set%sExportTimestampStart', $this->name);
		PlentymarketsConfig::getInstance()->$method($start);
	}

	/**
	 * Sets the finished timestamp
	 *
	 * @param integer $finished
	 */
	public function setFinished($finished)
	{
		$method = sprintf('set%sExportTimestampFinished', $this->name);
		PlentymarketsConfig::getInstance()->$method($finished);
	}

	/**
	 * Sets the error message
	 *
	 * @param string $error
	 */
	public function setError($error)
	{
		$method = sprintf('set%sExportLastErrorMessage', $this->name);
		PlentymarketsConfig::getInstance()->$method($error);
	}
}
