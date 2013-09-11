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

require_once PY_COMPONENTS . 'Mapping/Exception/PlentymarketsMappingExceptionNotExistant.php';

/**
 * PlentymarketsMappingEntityAbstract bequeaths mapping methods, which are used in all mapping entities.
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
abstract class PlentymarketsMappingEntityAbstract
{

	/**
	 *
	 * @var PDOStatement
	 */
	protected $StatementDeleteByShopwareID;

	/**
	 *
	 * @var PDOStatement
	 */
	protected $StatementAdd;

	/**
	 *
	 * @var PDOStatement
	 */
	protected $StatementGetByShopwareID;

	/**
	 *
	 * @var PDOStatement
	 */
	protected $StatementGetByPlentyID;

	/**
	 *
	 * @var array
	 */
	protected $cacheByShopwareID = array();

	/**
	 *
	 * @var array
	 */
	protected $cacheByPlentyID = array();

	/**
	 *
	 * @var array[PlentymarketsMappingEntityAbstract]
	 */
	protected static $Instances;

	/**
	 * protected constructor!
	 */
	protected function __construct()
	{
	}

	/**
	 * Initializes the statements
	 */
	protected function init()
	{
		$this->StatementAdd = Shopware()->Db()->prepare('
			INSERT INTO ' . $this->getName() . '
				SET
					shopwareID	= ?,
					plentyID	= ?
		');

		$this->StatementGetByShopwareID = Shopware()->Db()->prepare('
			SELECT plentyID
				FROM ' . $this->getName() . '
				WHERE shopwareID = ?
		');

		$this->StatementDeleteByShopwareID = Shopware()->Db()->prepare('
			DELETE FROM ' . $this->getName() . '
				WHERE shopwareID = ?
		');

		$this->StatementGetByPlentyID = Shopware()->Db()->prepare('
			SELECT shopwareID
				FROM ' . $this->getName() . '
				WHERE plentyID = ?
		');
	}

	/**
	 * Initialized the mapping data
	 */
	protected function initData()
	{
		$Query = Shopware()->Db()->query('
			SELECT shopwareID, plentyID
				FROM ' . $this->getName() . '
		');

		while (($data = $Query->fetch()) && is_array($data))
		{
			$this->setCache($data['shopwareId'], $data['plentyId']);
		}
	}

	/**
	 * No cloning is allowed
	 */
	protected function __clone()
	{
	}

	/**
	 * Returns the name of the database table
	 * 
	 * @return string
	 */
	abstract protected function getName();

	/**
	 * Returns an Instance
	 *
	 * @return PlentymarketsMappingEntityAbstract
	 */
	public final static function getInstance()
	{
		$class = get_called_class();
		if (!array_key_exists($class, self::$Instances))
		{
			self::$Instances[$class] = new $class();
			self::$Instances[$class]->init();
		}
		return self::$Instances[$class];
	}

	/**
	 * 
	 * Returns the mapping value for the plentymarkets id
	 *
	 * @param integer $plentyID
	 * @throws PlentymarketsMappingExceptionNotExistant
	 * @return integer
	 */
	public function getByPlentyID($plentyID)
	{
		if (array_key_exists($plentyID, $this->cacheByPlentyID))
		{
			return $this->cacheByPlentyID[$plentyID];
		}

		$this->StatementGetByPlentyID->execute(array(
			$plentyID
		));

		$shopwareID = $this->StatementGetByPlentyID->fetchColumn(0);
		if ($shopwareID === false)
		{
			throw new PlentymarketsMappingExceptionNotExistant(get_class($this) . '-plentyId:' . $plentyID);
		}

		if (is_numeric($shopwareID))
		{
			settype($shopwareID, 'integer');
		}

		$this->setCache($shopwareID, $plentyID);

		return $shopwareID;
	}

	/**
	 * Returns the mapping value for the shopware id
	 *
	 * @param $shopwareID $plentyID
	 * @throws PlentymarketsMappingExceptionNotExistant
	 * @return integer
	 */
	public function getByShopwareID($shopwareID)
	{
		if (array_key_exists($shopwareID, $this->cacheByShopwareID))
		{
			return $this->cacheByShopwareID[$shopwareID];
		}

		$this->StatementGetByShopwareID->execute(array(
			$shopwareID
		));

		$plentyID = $this->StatementGetByShopwareID->fetchColumn(0);
		if ($plentyID === false)
		{
			throw new PlentymarketsMappingExceptionNotExistant(get_class($this) . '-shopwareId:' . $shopwareID);
		}

		$this->setCache($shopwareID, $plentyID);

		return $plentyID;
	}

	/**
	 * Delete a mapping by a shopware id
	 *
	 * @param integer $shopwareID
	 * @throws PlentymarketsMappingExceptionNotExistant
	 */
	public function deleteByShopwareID($shopwareID)
	{
		if (array_key_exists($shopwareID, $this->cacheByShopwareID))
		{
			$plentyID = $this->cacheByShopwareID[$shopwareID];
			unset($this->cacheByShopwareID[$shopwareID], $this->cacheByPlentyID[$plentyID]);
		}

		$this->StatementDeleteByShopwareID->execute(array(
			$shopwareID
		));
	}

	/**
	 * Adds a mapping to the database and the internal cache
	 *
	 * @param integer $shopwareID
	 * @param integer $plentyID
	 * @throws Exception
	 */
	public function add($shopwareID, $plentyID, $deleteShopwareFirst = false)
	{
		if ($deleteShopwareFirst)
		{
			$this->deleteByShopwareID($shopwareID);
		}

		if (array_key_exists($shopwareID, $this->cacheByShopwareID))
		{
			return true;
		}

		try
		{
			$this->StatementAdd->execute(array(
				$shopwareID,
				$plentyID
			));
		}
		catch (Zend_Db_Statement_Exception $E)
		{
			// 23000 = Integrity constraint violation = Mapping already exists
			if ($E->getCode() !== 23000)
			{
				throw $E;
			}

			return false;
		}

		$this->setCache($shopwareID, $plentyID);

		return true;
	}

	/**
	 * Writes a mapping into the internal cache
	 *
	 * @param integer $shopwareID
	 * @param integer $plentyID
	 */
	protected function setCache($shopwareID, $plentyID)
	{
		$this->cacheByShopwareID[$shopwareID] = $plentyID;
		$this->cacheByPlentyID[$plentyID] = $shopwareID;
	}
}
