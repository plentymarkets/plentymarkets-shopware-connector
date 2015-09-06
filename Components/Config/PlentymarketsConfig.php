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
 * The class PlentymarketsConfig enables setting and getting config data on the basis of PHPs "magic methods" functionality.
 * In this way it is possible to do all the data mapping by using all needed SOAP-calls. To get more information on PHPs
 * magic methods please have a look at it's documentation.
 *
 * @link http://php.net/manual/en/language.oop5.magic.php
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsConfig
{

	/**
	 * Config data array.
	 *
	 * @var array
	 */
	protected $config = array();

	/**
	 * PlentymarketsConfig object data.
	 *
	 * @var PlentymarketsConfig
	 */
	protected static $Instance;

	/**
	 * Constructor, which loads all key value pairs of config data from database to prepare the config data array.
	 */
	public function __construct()
	{
		$Result = Shopware()->Db()->query('
			SELECT
					`key`, `value`
				FROM plenty_config
		');

		while (($config = $Result->fetchObject()) && is_object($config))
		{
			$this->config[$config->key] = $config->value;
		}
	}

	/**
	 * __call overloads methods to get, set or erase different config data.
	 * In case of setting data, the data in database as well as the data in instance cache will be updated.
	 *
	 * @param string $name
	 * @param array $args
	 * @return null string integer
	 */
	public function __call($name, $args)
	{
		if (strpos($name, 'get') === 0)
		{
			$key = substr($name, 3);
			return $this->get($key, $args[0] ?: null);
		}

		else if (strpos($name, 'set') === 0)
		{
			$key = substr($name, 3);

			if (!isset($args[0]))
			{
				return null;
			}

			$value = (string) $args[0];

			$this->set($key, $value);
		}

		else if (strpos($name, 'erase') === 0)
		{
			$key = substr($name, 5);
			$this->erase($key);
		}
	}

	/**
	 * Erases the given key from the config
	 *
	 * @param string $key
	 */
	public function erase($key)
	{
		// Clear cache
		unset($this->config[$key]);

		// Delete to database
		Shopware()->Db()->query('
				DELETE FROM plenty_config
					WHERE
						`key` = ?
			', array(
					$key
		));
	}

	/**
	 * Returns the value of the given key
	 * or the default value, if the key does not exist
	 *
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public function get($key, $default=null)
	{
		if (!isset($this->config[$key]))
		{
			return $default;
		}
		else
		{
			return $this->config[$key];
		}
	}

	/**
	 * Sets the konfig key to the given value
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function set($key, $value)
	{
		if ($this->config[$key] == $value)
		{
			return;
		}

		// Save to database
		Shopware()->Db()->query('
				REPLACE INTO plenty_config
					SET
						`key` = ?,
						`value` = ?
			', array(
					$key,
					$value
		));

		// Update the instance cache
		$this->config[$key] = $value;
	}

	/**
	 * Checks if all important data is complete.
	 *
	 * @return boolean
	 */
	public function isComplete()
	{
		return (
			!is_null($this->getOutgoingItemsIntervalID()) &&
			!is_null($this->getItemWarehousePercentage()) &&
			!is_null($this->getItemWarehouseID()) &&
			!is_null($this->getItemCleanupActionID()) &&
			!is_null($this->getDefaultCustomerGroupKey()) &&
			(!is_null($this->getItemProducerID()) && $this->getItemProducerID() > 0) &&
			!is_null($this->getOrderReferrerID()) &&
			!is_null($this->getOrderPaidStatusID()) &&
			!is_null($this->getOutgoingItemsID()) &&
			!is_null($this->getOutgoingItemsShopwareOrderStatusID()) &&
			!is_null($this->getReversalShopwareOrderStatusID()) &&
			!is_null($this->getIncomingPaymentShopwarePaymentFullStatusID()) &&
			!is_null($this->getIncomingPaymentShopwarePaymentPartialStatusID())
		);
	}

	/**
	 * Shortcut for item mesure units.
	 *
	 * @return array
	 */
	public function getItemMeasureUnits()
	{
		return unserialize($this->getItemMeasureUnitsSerialized());
	}

	/**
	 * Returns an sorted array of countries.
	 *
	 * @return array
	 */
	public function getMiscCountriesSorted()
	{
		$countries = unserialize($this->getMiscCountriesSerialized());
		usort($countries, function ($a, $b)
		{
			return strnatcmp($a["name"], $b["name"]);
		});

		return $countries;
	}

	/**
	 * Returns an sorted array of currencies.
	 *
	 * @return array
	 */
	public function getMiscCurrenciesSorted()
	{
		$currencies = unserialize($this->getMiscCurrenciesSerialized());
		usort($currencies, 'strnatcmp');

		$c = array();
		foreach ($currencies as $currency)
		{
			$c[$currency] = array(
				'id' => $currency,
				'name' => $currency
			);
		}
		return $c;
	}

	/**
	 * Returns an array of countries.
	 *
	 * @return array
	 */
	public function getMiscCountries()
	{
		return unserialize($this->getMiscCountriesSerialized());
	}

	/**
	 * Returns an array of order referrers.
	 *
	 * @return array
	 */
	public function getOrderReferrer()
	{
		return unserialize($this->getOrderReferrerSerialized());
	}

	/**
	 * If an instance of PlentymarketsConfig exists, it returns this instance.
	 * Else it creates a new instance of PlentymarketsConfig.
	 *
	 * @return PlentymarketsConfig
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
	 * Returns array of the config data.
	 *
	 * @return array
	 */
	public function getConfig()
	{
		return $this->config;
	}
}
