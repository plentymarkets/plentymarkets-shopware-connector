<?php
/**
 * plentymarkets shopware connector
 * Copyright © 2013 plentymarkets GmbH.
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

/**
 * Represents the status of an initial export.
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsExportStatus
{
    /**
     * @var string
     */
    const STATUS_OPEN = 'open';

    /**
     * @var string
     */
    const STATUS_SUCCESS = 'success';

    /**
     * @var string
     */
    const STATUS_PENDING = 'pending';

    /**
     * @var string
     */
    const STATUS_ERROR = 'error';

    /**
     * @var string
     */
    const STATUS_RUNNING = 'running';

    /**
     * @var int
     */
    const SECONDS_OVERDUE = 900;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var bool
     */
    protected $isOptional = false;

    /**
     * I am the contructor.
     *
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Checks whether the export is finished.
     *
     * @return bool
     */
    public function isFinished()
    {
        return $this->getStatus() == self::STATUS_SUCCESS;
    }

    /**
     * Checks whether the export may be announced.
     *
     * @return bool
     */
    public function mayAnnounce()
    {
        return $this->getStatus() == self::STATUS_OPEN;
    }

    /**
     * Checks whether the export may be erased.
     *
     * @return bool
     */
    public function mayErase()
    {
        return $this->getStart() != -1;
    }

    /**
     * Checks whether the export may be resetted.
     *
     * @return bool
     */
    public function mayReset()
    {
        return $this->getStatus() != self::STATUS_OPEN;
    }

    /**
     * Checks whether the export depends on another export.
     *
     * @return bool
     */
    public function needsDependency()
    {
        return false;
    }

    /**
     * Sets the export as optional.
     */
    public function setOptional()
    {
        $this->isOptional = true;
    }

    /**
     * Checks whether the export is blocked.
     *
     * @return bool
     */
    public function isBlocking()
    {
        return $this->getStatus() == self::STATUS_PENDING || $this->getStatus() == self::STATUS_RUNNING;
    }

    /**
     * Checks whether the export is broken.
     *
     * @return bool
     */
    public function isBroke()
    {
        return $this->getStatus() == self::STATUS_ERROR;
    }

    /**
     * Checks whether the export is waiting.
     *
     * @return bool
     */
    public function isWaiting()
    {
        return $this->getStatus() == self::STATUS_PENDING;
    }

    /**
     * Checks whether the export is optional.
     *
     * @return bool
     */
    public function isOptional()
    {
        return $this->isOptional;
    }

    /**
     * Checks whether the next call is overdue.
     *
     * @return bool
     */
    public function isOverdue()
    {
        if ($this->getStatus() != self::STATUS_RUNNING) {
            return false;
        }

        $lastCallTimestamp = (int) PlentymarketsConfig::getInstance()->getInitialExportLastCallTimestamp();

        if ($lastCallTimestamp == 0) {
            return false;
        }

        return $lastCallTimestamp < (time() - self::SECONDS_OVERDUE);
    }

    /**
     * Returns the name of the export.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the status of the export.
     *
     * @return string
     */
    public function getStatus()
    {
        $method = sprintf('get%sExportStatus', $this->name);

        return PlentymarketsConfig::getInstance()->$method(self::STATUS_OPEN);
    }

    /**
     * Returns the start timestmap.
     *
     * @return int
     */
    public function getStart()
    {
        $method = sprintf('get%sExportTimestampStart', $this->name);

        return (int) PlentymarketsConfig::getInstance()->$method(-1);
    }

    /**
     * Returns the finshed timestamp.
     *
     * @return int
     */
    public function getFinished()
    {
        $method = sprintf('get%sExportTimestampFinished', $this->name);

        return (int) PlentymarketsConfig::getInstance()->$method(-1);
    }

    /**
     * Returns the error message.
     *
     * @return string
     */
    public function getError()
    {
        $method = sprintf('get%sExportLastErrorMessage', $this->name);

        return (string) PlentymarketsConfig::getInstance()->$method();
    }

    /**
     * Sets the name of the export.
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Sets the status of the export.
     *
     * @param string $status
     */
    public function setStatus($status)
    {
        $method = sprintf('set%sExportStatus', $this->name);
        PlentymarketsConfig::getInstance()->$method($status);
    }

    /**
     * Sets the start timestmamp.
     *
     * @param int $start
     */
    public function setStart($start)
    {
        $method = sprintf('set%sExportTimestampStart', $this->name);
        PlentymarketsConfig::getInstance()->$method($start);
    }

    /**
     * Sets the finished timestamp.
     *
     * @param int $finished
     */
    public function setFinished($finished)
    {
        $method = sprintf('set%sExportTimestampFinished', $this->name);
        PlentymarketsConfig::getInstance()->$method($finished);
    }

    /**
     * Sets the error message.
     *
     * @param string $error
     */
    public function setError($error = null)
    {
        if ($error) {
            // Automatically set the status
            $this->setStatus(self::STATUS_ERROR);

            $method = sprintf('set%sExportLastErrorMessage', $this->name);
            PlentymarketsConfig::getInstance()->$method($error);
        } else {
            $method = sprintf('erase%sExportLastErrorMessage', $this->name);
            PlentymarketsConfig::getInstance()->$method();
        }
    }

    /**
     * Announces the export.
     */
    public function announce()
    {
        $this->setStatus(self::STATUS_PENDING);
    }

    /**
     * Resets the status of the export.
     */
    public function reset()
    {
        $this->setStatus(self::STATUS_OPEN);
        $this->setStart(0);
        $this->setFinished(0);
        $this->setError();

        // Last chunk
        $key = sprintf('%sExportLastChunk', $this->getName());
        PlentymarketsConfig::getInstance()->erase($key);
    }

    /**
     * Erases the status of the export.
     */
    public function erase()
    {
        $this->reset();
        $this->setStart(-1);
    }
}
