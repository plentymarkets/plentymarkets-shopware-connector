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


// Dependencies
require_once PY_SOAP . 'Models/PlentySoapResponseMessage.php';
require_once PY_SOAP . 'Models/PlentySoapResponseSubMessage.php';
require_once PY_SOAP . 'PlentymarketsSoapConnectionException.php';

/**
 * The class PlentymarketsSoapClient is used in most classes of the plentymarkets plugin. It provides all
 * needed SOAP-Calls for cronjobs, exports, imports and controllers in the newest version 110.
 * SOAP-Calls are used for data communication between a plentymarkets backend system and a client.
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsSoapClient extends SoapClient
{
	/**
	 *
	 * @var integer
	 */
	const NUMBER_OF_RETRIES_MAX = 3;

	/**
	 *
	 * @var integer
	 */
	const NUMBER_OF_SECONDS_SLEEP = 5;

	/**
	 *
	 * @var integer
	 */
	const NUMBER_OF_SECONDS_SLEEP_CONNECTION = 1;

	/**
	 *
	 * @var PlentymarketsSoapClient
	 */
	protected static $Instance;

	/**
	 *
	 * @var PlentymarketsConfig
	 */
	protected $Config;

	/**
	 *
	 * @var string
	 */
	protected $wsdl;

	/**
	 *
	 * @var unknown
	 */
	protected $username;

	/**
	 *
	 * @var string
	 */
	protected $userpass;

	/**
	 *
	 * @var boolean
	 */
	protected $dryrun = false;

	/**
	 *
	 * @var integer
	 */
	protected $userId;

	/**
	 *
	 * @var string
	 */
	protected $userToken;

	/**
	 *
	 * @var string
	 */
	protected $timestampConfigKey;

	/**
	 *
	 * @var integer
	 */
	protected $numberOfCalls = 0;

	/**
	 * Constructor method
	 *
	 * @param string $wsdl
	 * @param string $username
	 * @param string $userpass
	 * @param bool $dryrun
	 * @throws PlentymarketsSoapConnectionException
	 * @return PlentymarketsSoapClient
	 */
	protected function __construct($wsdl, $username, $userpass, $dryrun = false)
	{
		// Set the connection timeout
		if (function_exists('ini_set'))
		{
			ini_set('default_socket_timeout', 60);
		}

		// Get the config
		$this->Config = PlentymarketsConfig::getInstance();

		//
		$this->wsdl = $wsdl;
		$this->username = $username;
		$this->userpass = $userpass;
		$this->dryrun = (bool) $dryrun;

		// Options
		$options = array();
		$options['features'] = SOAP_SINGLE_ELEMENT_ARRAYS;
		$options['version'] = SOAP_1_2;
		$options['encoding'] = 'utf-8';
		$options['exceptions'] = true;
		$options['trace'] = true;
		$options['connection_timeout'] = 10;

		// PHP 5.4
		// $options['keep_alive'] = false;

		// Cache
		if ($_SERVER['SERVER_ADDR'] != '127.0.0.1')
		{
			$options['cache_wsdl'] = WSDL_CACHE_NONE;
		}

		// Compression
		if ($this->Config->getApiUseGzipCompression(false))
		{
			$options['compression'] = SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP;
		}

		// HTTP 1.0 to send "Connection: close"
		$context = stream_context_create(array(
			'http' => array(
				'protocol_version' => 1.0
			))
		);
		$options['stream_context'] = $context;

		// Init the client
		$retries = 0;
		do
		{
			try
			{
				@parent::__construct($wsdl, $options);
				break;
			}
			catch (SoapFault $E)
			{
				++$retries;

				if ($retries == 3)
				{
					throw new PlentymarketsSoapConnectionException();
				}

				sleep($retries * self::NUMBER_OF_SECONDS_SLEEP_CONNECTION);
			}
		}
		while ($retries < self::NUMBER_OF_RETRIES_MAX);

		// Check whether auth cache exist and whether the file is from today
		if (!$dryrun && date('Y-m-d', $this->Config->getApiLastAuthTimestamp(0)) == date('Y-m-d'))
		{
			// Get auth data from the config
			$this->userId		= $this->Config->getApiUserID('');
			$this->userToken	= $this->Config->getApiToken('');
		}

		else
		{
			// Get a new token
			$this->getToken();
		}

		// Set the new token
		$this->setSoapHeaders();
	}

	/**
	 * Retrieves a new plentymarkets API token
	 *
	 * @throws Exception
	 */
	private function getToken()
	{
		// Load the request model
		require_once PY_SOAP . 'Models/PlentySoapRequest/GetAuthentificationToken.php';

		// Authentication
		$Request_GetAuthentificationToken = new PlentySoapRequest_GetAuthentificationToken();
		$Request_GetAuthentificationToken->Username = $this->username;
		$Request_GetAuthentificationToken->Userpass = $this->userpass;

		$Response_GetAuthentificationToken = $this->GetAuthentificationToken($Request_GetAuthentificationToken);

		if ($Response_GetAuthentificationToken->Success == true)
		{
			$this->userId = $Response_GetAuthentificationToken->UserID;
			$this->userToken = $Response_GetAuthentificationToken->Token;

			if (!$this->dryrun)
			{
				// Save the auth data
				$this->Config->setApiUserID($this->userId);
				$this->Config->setApiToken($this->userToken);
				$this->Config->setApiLastAuthTimestamp(time());

				// Log
				PlentymarketsLogger::getInstance()->message('Soap:Auth', 'Received a new token');
			}
		}
		else
		{
			$this->userId = -1;
			$this->userToken = '';

			// Log invalid api data
			PlentymarketsLogger::getInstance()->message('Soap:Auth', 'Invalid API credentials');

			// Quit
			throw new \Exception('Invalid API credentials');
		}
	}

	/**
	 * Sets the soap authentication header
	 */
	private function setSoapHeaders()
	{
		// Auth data
		$authentication = array(
			'UserID' => $this->userId,
			'Token' => $this->userToken
		);

		// Add the authentication header
		$this->__setSoapHeaders(
			new SoapHeader(substr($this->wsdl, 0, -4), 'verifyingToken', new SoapVar($authentication, SOAP_ENC_OBJECT))
		);
	}

	/**
	 * Performes a SOAP call
	 *
	 * @see SoapClient::__call()
	 */
	public function __call($call, $args)
	{
		$retries = 0;

		do
		{
			try
			{
				// Call
				$Response = $this->doCall($call, $args);

				// Quit the loop on success
				break;
			}
			catch (Exception $E)
			{
				++$retries;

				// Calculate seconds based on the number of retries
				$seconds = self::NUMBER_OF_SECONDS_SLEEP * $retries;

				// Try to get a new token
				if ($E->getMessage() == 'Unauthorized Request - Invalid Token')
				{
					// Log the error
					PlentymarketsLogger::getInstance()->error('Soap:Call', $call . ' failed: Unauthorized Request - Invalid Token', 1110);

					// Refresh the token
					$this->getToken();
					$this->setSoapHeaders();
				}

				else
				{
					PlentymarketsLogger::getInstance()->message('Soap:Call', $call . ' will wait ' . $seconds . ' seconds and then try again (' . $retries . '/' . self::NUMBER_OF_RETRIES_MAX . ')');
					sleep($seconds);
				}
			}
		}
		while ($retries < self::NUMBER_OF_RETRIES_MAX);

		// Log the call's success state
		if (isset($Response->Success) && $Response->Success == true)
		{
			if ($call == 'GetServerTime')
			{
				if (!$this->Config->getApiIgnoreGetServerTime())
				{
					PlentymarketsLogger::getInstance()->message('Soap:Call', 'GetServerTime success');
				}
			}
			else
			{
				PlentymarketsLogger::getInstance()->message('Soap:Call', $call . ' success');
			}

			// Remember the timestamp
			if (!empty($this->timestampConfigKey))
			{
				$this->Config->set($this->timestampConfigKey, time());
			}
		}
		else
		{
			PlentymarketsLogger::getInstance()->error('Soap:Call', $call . ' failed', 1100);
			if (isset($Response) && $this->Config->getApiLogHttpHeaders(false))
			{
				PlentymarketsLogger::getInstance()->error('Soap:Call', var_export($Response, true));
			}
			if (isset($E) && $E instanceof Exception)
			{
				PlentymarketsLogger::getInstance()->error('Soap:Call:Request', htmlspecialchars($this->__getLastRequest()));
				PlentymarketsLogger::getInstance()->error('Soap:Call', $E->getMessage());
			}
		}

		// Log the HTTP headers?
		if ($this->Config->getApiLogHttpHeaders(false))
		{
			PlentymarketsLogger::getInstance()->message('Soap:Call:Header:Request', $this->__getLastRequestHeaders());
			PlentymarketsLogger::getInstance()->message('Soap:Call:Header:Response', $this->__getLastResponseHeaders());
		}

		++$this->numberOfCalls;

		return $Response;
	}

	/**
	 * Wrapper for the actual soap call
	 *
	 * @param string $call
	 * @param array $args
	 * @return mixed
	 */
	protected function doCall($call, $args)
	{
		if (count($args))
		{
			$Response = parent::__soapCall($call, array($args[0]));
		}
		else
		{
			$Response = parent::__soapCall($call, array());
		}

		return $Response;
	}

	/**
	 * Returns an instance
	 * @return PlentymarketsSoapClient
	 */
	public static function getInstance()
	{
		if (!self::$Instance instanceof self)
		{
			// Confiug
			$PlentymarketsConfig = PlentymarketsConfig::getInstance();

			// WSDL
			$wsdl = $PlentymarketsConfig->getApiWsdl() . '/plenty/api/soap/version110/?xml';

			//
			self::$Instance = new self($wsdl, $PlentymarketsConfig->getApiUsername(), $PlentymarketsConfig->getApiPassword());
		}

		return self::$Instance;
	}

	/**
	 * gets the timestamp config key
	 *
	 * @return string
	 */
	public function getTimestampConfigKey()
	{
		return $this->timestampConfigKey;
	}

	/**
	 * Sets the timestamp config key
	 *
	 * @param string $timestampConfigKey
	 */
	public function setTimestampConfigKey($timestampConfigKey)
	{
		$this->timestampConfigKey = (string) $timestampConfigKey;
	}

	/**
	 * Returns a dummy instance
	 *
	 * @param string $wsdl
	 * @param string $username
	 * @param $password
	 * @internal param string $userpass
	 * @return PlentymarketsSoapClient
	 */
	public static function getTestInstance($wsdl, $username, $password)
	{
		return new self($wsdl, $username, $password, true);
	}

	/**
	 * Removes control chars from the given string except tab and crln
	 *
	 * @param string $string
	 * @return string
	 */
	public static function removeControlChars($string)
	{
		return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $string);
	}

	/**
	 * Returns the number of calls
	 *
	 * @return integer
	 */
	public function getNumberOfCalls()
	{
		return $this->numberOfCalls;
	}

	/**
	 * Sets the number of calls
	 *
	 * @param integer $numberOfCalls
	 */
	public function setNumberOfCalls($numberOfCalls)
	{
		$this->numberOfCalls = $numberOfCalls;
	}

}
