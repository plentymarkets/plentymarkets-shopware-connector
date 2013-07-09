<?php
require_once __DIR__ . '/../Exception/PlentymarketsMappingExceptionNotExistant.php';

/**
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
	 */
	protected function __construct()
	{
	}

	/**
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
	 */
	protected function __clone()
	{
	}

	/**
	 *
	 * @return string
	 */
	abstract protected function getName();

	/**
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
