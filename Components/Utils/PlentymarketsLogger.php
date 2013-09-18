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
 * The main functionality of this class is to log error messages or other messages. Both error messages and other messages are
 * defined in two groups, soap log messages and all other messages. This class is used in the most classes of the plentymarkets
 * plugin.
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsLogger
{

	/**
	 *
	 * @var integer
	 */
	const TYPE_ERROR = 1;

	/**
	 *
	 * @var integer
	 */
	const TYPE_MESSAGE = 2;
	
	/**
	 * 
	 * @var string
	 */
	const PREFIX_UPDATE = 'Update';

	/**
	 *
	 * @var DB2Statement
	 */
	protected $StatementInsert = null;

	/**
	 *
	 * @var PlentymarketsLogger
	 */
	protected static $Instance = null;

	/**
	 * Create the insert statement
	 */
	public function __construct()
	{
		$this->StatementInsert = Shopware()->Db()->prepare('
			INSERT INTO plenty_log
				SET
					`timestamp` = ' . time() . ',
					type = ?,
					identifier = ?,
					message = ?
		');
	}

	/**
	 * Returns an instance
	 *
	 * @return PlentymarketsLogger
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
	 * Returns a page of the log
	 *
	 * @param unknown $start
	 * @param unknown $limit
	 * @param number $type
	 * @return multitype:NULL Ambigous <multitype:, multitype:mixed Ambigous <string, boolean, mixed> >
	 */
	public function get($start, $limit, $type = 0, $filter = '')
	{
		$where = 'WHERE 1';
		
		if ($type > 0)
		{
			$where .= ' AND type = ' . (integer) $type;
		}
		
		if (strlen($filter) > 4)
		{
			$where .= ' AND identifier = "'. $filter .'"';
		}
		

		$limit = ' LIMIT ' . $start . ', ' . $limit;

		$Result = Shopware()->Db()->query('
			SELECT
					SQL_CALC_FOUND_ROWS *
				FROM plenty_log
					' . $where . '
				ORDER BY id DESC
					' . $limit . '
		');

		return array(
			'data' => $Result->fetchAll(),
			'total' => Shopware()->Db()->query('
				SELECT FOUND_ROWS()
			')->fetchColumn(0)
		);
	}
	
	/**
	 * Returns all identifiers
	 * @return array
	 */
	public function getIdentifierList()
	{
		return Shopware()->Db()->query('
			SELECT
					DISTINCT identifier
				FROM plenty_log
				ORDER BY identifier ASC
		')->fetchAll();
	}

	/**
	 * Create a new log entry
	 *
	 * @param integer $type
	 * @param string $identifier
	 * @param string $message
	 */
	protected function log($type, $identifier, $message)
	{
		$this->StatementInsert->execute(array(
			$type,
			$identifier,
			$message
		));
	}

	/**
	 * Log an error message
	 *
	 * @param string $identifier
	 * @param string $message
	 */
	public function error($identifier, $message)
	{
		$this->log(self::TYPE_ERROR, $identifier, $message);
	}

	/**
	 * Logs a new message
	 *
	 * @param string $identifier
	 * @param string $message
	 */
	public function message($identifier, $message)
	{
		$this->log(self::TYPE_MESSAGE, $identifier, $message);
	}
}
