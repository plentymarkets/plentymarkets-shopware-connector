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

/**
 * Represents the status of an initial export
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
interface PlentymarketsExportStatusInterface
{

	/**
	 * Interface method
	 */
	public function isFinished();

	/**
	 * Interface method
	 */
	public function isBlocking();

	/**
	 * Interface method
	 */
	public function isBroke();

	/**
	 * Interface method
	 */
	public function isWaiting();

	/**
	 * Interface method
	 */
	public function isOptional();

	/**
	 * Interface method
	 */
	public function isOverdue();

	/**
	 * Interface method
	 */
	public function mayAnnounce();

	/**
	 * Interface method
	 */
	public function mayReset();

	/**
	 * Interface method
	 */
	public function mayErase();

	/**
	 * Interface method
	 */
	public function needsDependency();

	/**
	 * Interface method
	 */
	public function getName();

	/**
	 * Interface method
	 */
	public function getStatus();

	/**
	 * Interface method
	 */
	public function getStart();

	/**
	 * Interface method
	 */
	public function getFinished();

	/**
	 * Interface method
	 */
	public function getError();
}
