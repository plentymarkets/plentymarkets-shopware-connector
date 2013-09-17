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
	 * @var PlentymarketsSoapClient
	 */
	protected static $Instance;

	/**
	 * Constructor method
	 *
	 * @param string $wsdl
	 * @param string $username
	 * @param string $userpass
	 * @return PlentymarketsSoapClient
	 */
	protected function __construct($wsdl, $username, $userpass, $dryrun = false)
	{
		// Options
		$options = array();
		$options['features'] = SOAP_SINGLE_ELEMENT_ARRAYS;
		$options['version'] = SOAP_1_2;
		if ($_SERVER['SERVER_ADDR'] != '127.0.0.1')
		{
			$options['cache_wsdl'] = WSDL_CACHE_NONE;
		}
		$options['exceptions'] = true;
		$options['trace'] = true;

		//
		parent::__construct($wsdl, $options);

		// Check whether auth cache exist and whether the file is from today
		if (!$dryrun && date('Y-m-d', PlentymarketsConfig::getInstance()->getApiLastAuthTimestamp(0)) == date('Y-m-d'))
		{
			$userID = PlentymarketsConfig::getInstance()->getApiUserID(-1);
			$token = PlentymarketsConfig::getInstance()->getApiToken('unknown');
		}
		else
		{
			// Load the request model
			require_once PY_SOAP . 'Models/PlentySoapRequest/GetAuthentificationToken.php';

			// Authentication
			$Request_GetAuthentificationToken = new PlentySoapRequest_GetAuthentificationToken();
			$Request_GetAuthentificationToken->Username = $username;
			$Request_GetAuthentificationToken->Userpass = $userpass;

			$Response_GetAuthentificationToken = $this->GetAuthentificationToken($Request_GetAuthentificationToken);

			if ($Response_GetAuthentificationToken->Success == true)
			{
				$userID = $Response_GetAuthentificationToken->UserID;
				$token = $Response_GetAuthentificationToken->Token;

				if (!$dryrun)
				{
					PlentymarketsConfig::getInstance()->setApiUserID($userID);
					PlentymarketsConfig::getInstance()->setApiToken($token);
					PlentymarketsConfig::getInstance()->setApiLastAuthTimestamp(time());

					// Log
					PlentymarketsLogger::getInstance()->message('Soap:Auth', 'Received a new token');
				}
				else
				{
					// Log
					PlentymarketsLogger::getInstance()->message('Soap:Auth', 'API credentials tested successully');
				}
			}
			else
			{
				if (!$dryrun)
				{
					PlentymarketsLogger::getInstance()->message('Soap:Auth', 'Invalid API credentials');
				}
				else
				{
					PlentymarketsLogger::getInstance()->message('Soap:Auth', 'Invalid API credentials');
				}

				throw new Exception('Credentials invalid');

			}
		}

		//
		$authentication = array(
			'UserID' => $userID,
			'Token' => $token
		);
		
		//
		$this->__setSoapHeaders(new SoapHeader(substr($wsdl, 0, -4), 'verifyingToken', new SoapVar($authentication, SOAP_ENC_OBJECT)));
	}

	/**
	 * Performes a SOAP call
	 * 
	 * @see SoapClient::__call()
	 */
	public function __call($call, $args)
	{
		try
		{
			if (count($args))
			{
				$Response = parent::__soapCall($call, array($args[0]));
			}
			else
			{
				$Response = parent::__soapCall($call, array());
			}
		}
		catch (Exception $E)
		{
		}
		
		if (isset($Response->Success) && $Response->Success == true)
		{
			PlentymarketsLogger::getInstance()->message('Soap:Call', $call . ' success');
		}
		else
		{
			PlentymarketsLogger::getInstance()->error('Soap:Call', $call . ' failed');
			if (isset($E) && $E instanceof Exception)
			{
				PlentymarketsLogger::getInstance()->error('Soap:Call', $E->getMessage());
			}
		}

		return $Response;
	}

	/**
	 * Returns an instance
	 *
	 * @param string $wsdl
	 * @param string $username
	 * @param string $userpass
	 * @return PlentymarketsSoapClient
	 */
	public static function getInstance()
	{
		if (!self::$Instance instanceof self)
		{
			$PlentymarketsConfig = PlentymarketsConfig::getInstance();
			self::$Instance = new self($PlentymarketsConfig->getApiWsdl() . '/plenty/api/soap/version110/?xml', $PlentymarketsConfig->getApiUsername(), $PlentymarketsConfig->getApiPassword());
		}

		return self::$Instance;
	}

	/**
	 * Returns a dummy instance
	 *
	 * @param string $wsdl
	 * @param string $username
	 * @param string $userpass
	 * @return PlentymarketsSoapClient
	 */
	public static function getTestInstance($wsdl, $username, $password)
	{
		return new self($wsdl, $username, $password, true);
	}

}
