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
 * @copyright  Copyright (c) 2013, plentymarkets GmbH (http://www.plentymarkets.com)
 * @author     Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */

require_once PY_COMPONENTS . 'Utils/DataIntegrity/Check/PlentymarketsDataIntegrityCheckItemMainDetailLost.php';
require_once PY_COMPONENTS . 'Utils/DataIntegrity/Check/PlentymarketsDataIntegrityCheckItemOrphaned.php';
require_once PY_COMPONENTS . 'Utils/DataIntegrity/Check/PlentymarketsDataIntegrityCheckItemVariationGroupMultiple.php';
require_once PY_COMPONENTS . 'Utils/DataIntegrity/Check/PlentymarketsDataIntegrityCheckItemVariationOptionLost.php';

/**
 * Data integrity conroller class
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsDataIntegrityController
{

	/**
	 *
	 * @var PlentymarketsDataIntegrityController
	 */
	protected static $Instance;

	/**
	 *
	 * @var array[PlentymarketsDataIntegrityCheckInterface]
	 */
	protected $Checks = array();

	/**
	 * Adds all the checks to the controller
	 */
	protected function __construct()
	{
		$this->add(new PlentymarketsDataIntegrityCheckItemMainDetailLost());
		$this->add(new PlentymarketsDataIntegrityCheckItemOrphaned());
		$this->add(new PlentymarketsDataIntegrityCheckItemVariationGroupMultiple());
		$this->add(new PlentymarketsDataIntegrityCheckItemVariationOptionLost());
	}

	/**
	 * Returns an instance of PlentymarketsDataIntegrityController
	 *
	 * @return PlentymarketsDataIntegrityController
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
	 * Adds another check to the controller
	 *
	 * @param PlentymarketsDataIntegrityCheckInterface $Check
	 */
	public function add(PlentymarketsDataIntegrityCheckInterface $Check)
	{
		$this->Checks[$Check->getName()] = $Check;
	}

	/**
	 * Returns the checks
	 *
	 * @return multitype:PlentymarketsDataIntegrityCheckInterface
	 */
	public function getChecks()
	{
		return $this->Checks;
	}

	/**
	 * Checks whether the checks are valid
	 *
	 * @return boolean
	 */
	public function isValid()
	{
		foreach ($this->Checks as $Check)
		{
			if (!$Check->isValid())
			{
				return false;
			}
		}
		return true;
	}

	/**
	 * Returns an explicit check
	 *
	 * @param string $name
	 * @return PlentymarketsDataIntegrityCheckInterface
	 */
	public function getCheck($name)
	{
		return $this->Checks[$name];
	}
}
