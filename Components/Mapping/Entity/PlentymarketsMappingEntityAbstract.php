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

/**
 * PlentymarketsMappingEntityAbstract bequeaths mapping methods, which are used in all mapping entities.
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
abstract class PlentymarketsMappingEntityAbstract
{
    /**
     * @var PDOStatement
     */
    protected $StatementDeleteByShopwareID;

    /**
     * @var PDOStatement
     */
    protected $StatementAdd;

    /**
     * @var PDOStatement
     */
    protected $StatementGetByShopwareID;

    /**
     * @var PDOStatement
     */
    protected $StatementGetByPlentyID;

    /**
     * @var array
     */
    protected $cacheByShopwareID = [];

    /**
     * @var array
     */
    protected $cacheByPlentyID = [];

    /**
     * @var PlentymarketsMappingEntityAbstract[]
     */
    protected static $Instances;

    /**
     * protected constructor!
     */
    protected function __construct()
    {
    }

    /**
     * No cloning is allowed
     */
    protected function __clone()
    {
    }

    /**
     * Returns an Instance
     *
     * @return PlentymarketsMappingEntityAbstract
     */
    final public static function getInstance()
    {
        $class = get_called_class();
        if (!array_key_exists($class, self::$Instances)) {
            self::$Instances[$class] = new $class();
            self::$Instances[$class]->init();
        }

        return self::$Instances[$class];
    }

    /**
     * Returns the mapping value for the plentymarkets id
     *
     * @param int $plentyID
     *
     * @throws PlentymarketsMappingExceptionNotExistant
     *
     * @return int
     */
    public function getByPlentyID($plentyID)
    {
        if (array_key_exists($plentyID, $this->cacheByPlentyID)) {
            return $this->cacheByPlentyID[$plentyID];
        }

        $this->StatementGetByPlentyID->execute([
            $plentyID,
        ]);

        $shopwareID = $this->StatementGetByPlentyID->fetchColumn(0);
        if ($shopwareID === false) {
            throw new PlentymarketsMappingExceptionNotExistant(get_class($this) . '-plentyId:' . $plentyID);
        }

        if (is_numeric($shopwareID)) {
            settype($shopwareID, 'integer');
        }

        $this->setCache($shopwareID, $plentyID);

        return $shopwareID;
    }

    /**
     * Returns the mapping value for the shopware id
     *
     * @param $shopwareID $plentyID
     *
     * @throws PlentymarketsMappingExceptionNotExistant
     *
     * @return int
     */
    public function getByShopwareID($shopwareID)
    {
        if (array_key_exists($shopwareID, $this->cacheByShopwareID)) {
            return $this->cacheByShopwareID[$shopwareID];
        }

        $this->StatementGetByShopwareID->execute([
            $shopwareID,
        ]);

        $plentyID = $this->StatementGetByShopwareID->fetchColumn(0);
        if ($plentyID === false) {
            throw new PlentymarketsMappingExceptionNotExistant(get_class($this) . '-shopwareId:' . $shopwareID);
        }

        $this->setCache($shopwareID, $plentyID);

        return $plentyID;
    }

    /**
     * Delete a mapping by a shopware id
     *
     * @param int $shopwareID
     *
     * @throws PlentymarketsMappingExceptionNotExistant
     */
    public function deleteByShopwareID($shopwareID)
    {
        if (array_key_exists($shopwareID, $this->cacheByShopwareID)) {
            $plentyID = $this->cacheByShopwareID[$shopwareID];
            unset($this->cacheByShopwareID[$shopwareID], $this->cacheByPlentyID[$plentyID]);
        }

        $this->StatementDeleteByShopwareID->execute([
            $shopwareID,
        ]);
    }

    /**
     * Adds a mapping to the database and the internal cache
     *
     * @param int $shopwareID
     * @param int $plentyID
     * @param bool $deleteShopwareFirst
     *
     * @throws Exception
     * @throws Zend_Db_Statement_Exception
     *
     * @return bool
     */
    public function add($shopwareID, $plentyID, $deleteShopwareFirst = false)
    {
        if ($deleteShopwareFirst) {
            $this->deleteByShopwareID($shopwareID);
        }

        if (array_key_exists($shopwareID, $this->cacheByShopwareID)) {
            return true;
        }

        try {
            $this->StatementAdd->execute([
                $shopwareID,
                $plentyID,
            ]);
        } catch (Zend_Db_Statement_Exception $E) {
            // 23000 = Integrity constraint violation = Mapping already exists
            if ($E->getCode() !== 23000) {
                throw $E;
            }

            return false;
        }

        $this->setCache($shopwareID, $plentyID);

        return true;
    }

    /**
     * Clears the internal cache
     */
    public function clearCache()
    {
        $this->cacheByShopwareID = [];
        $this->cacheByPlentyID = [];
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

        while (($data = $Query->fetch()) && is_array($data)) {
            $this->setCache($data['shopwareId'], $data['plentyId']);
        }
    }

    /**
     * Returns the name of the database table
     *
     * @return string
     */
    abstract protected function getName();

    /**
     * Writes a mapping into the internal cache
     *
     * @param int $shopwareID
     * @param int $plentyID
     */
    protected function setCache($shopwareID, $plentyID)
    {
        $this->cacheByShopwareID[$shopwareID] = $plentyID;
        $this->cacheByPlentyID[$plentyID] = $shopwareID;
    }
}
