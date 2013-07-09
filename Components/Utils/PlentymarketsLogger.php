<?php

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
	 * @var DB2Statement
	 */
	protected $StatementInsert = null;

	/**
	 *
	 * @var PlentymarketsLogger
	 */
	protected static $Instance = null;

	/**
	 */
	public function __construct()
	{
		$this->StatementInsert = Shopware()->Db()->prepare('
			INSERT INTO plenty_log
				SET
					`timestamp` = ' . time() . ',
					type = ?,
					identifier = ?,
					message = ?,
					request = ?,
					response = ?
		');
	}

	/**
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
	 *
	 * @param unknown $start
	 * @param unknown $limit
	 * @param number $type
	 * @return multitype:NULL Ambigous <multitype:, multitype:mixed Ambigous <string, boolean, mixed> >
	 */
	public function get($start, $limit, $type = 0)
	{
		if ($type == 0)
		{
			$where = '';
		}
		else
		{
			$where = ' WHERE type = ' . (integer) $type;
		}

		$limit = ' LIMIT ' . $start . ', ' . $limit;

		$Result = Shopware()->Db()->query('
			SELECT
					SQL_CALC_FOUND_ROWS *,
					CONCAT("[", identifier, "] ", message) longmessage
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
	 *
	 * @param integer $type
	 * @param string $identifier
	 * @param string $message
	 */
	protected function log($type, $identifier, $message, $request='', $response='')
	{
		$this->StatementInsert->execute(array(
			$type,
			$identifier,
			$message,
			$request,
			$response
		));
	}

	public function callMessage($call, $request, $response)
	{
		$this->log(self::TYPE_MESSAGE, 'Soap:Call', $call . ' success'/*, $request, $response*/);
	}

	public function callError($call, $request, $response)
	{
		$this->log(self::TYPE_ERROR, 'Soap:Call', $call . ' failed'/*, $request, $response*/);
	}

	/**
	 *
	 * @param string $identifier
	 * @param string $message
	 */
	public function error($identifier, $message)
	{
		$this->log(self::TYPE_ERROR, $identifier, $message);
	}

	/**
	 *
	 * @param string $identifier
	 * @param string $message
	 */
	public function message($identifier, $message)
	{
		$this->log(self::TYPE_MESSAGE, $identifier, $message);
	}
}
