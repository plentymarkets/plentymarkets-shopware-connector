<?php

/**
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsConfig
{

	/**
	 *
	 * @var array
	 */
	protected $config = array();

	/**
	 *
	 * @var PlentymarketsConfig
	 */
	protected static $Instance;

	/**
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
			if (!array_key_exists($key, $this->config))
			{
				if (array_key_exists(0, $args))
				{
					return $args[0];
				}
				return null;
			}
			else
			{
				return $this->config[$key];
			}
		}

		else if (strpos($name, 'set') === 0)
		{
			$key = substr($name, 3);
			if (!array_key_exists(0, $args))
			{
				return;
			}

			if ($this->config[$key] == $args[0])
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
				$args[0]
			));

			// Update the instance cache
			$this->config[$key] = $args[0];
		}
	}

	/**
	 * Shortcut for item mesure units
	 *
	 * @return array
	 */
	public function getItemMeasureUnits()
	{
		return unserialize($this->getItemMeasureUnitsSerialized());
	}

	/**
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
	 *
	 * @return array
	 */
	public function getMiscCountries()
	{
		return unserialize($this->getMiscCountriesSerialized());
	}

	/**
	 *
	 * @return array
	 */
	public function getOrderReferrer()
	{
		return unserialize($this->getOrderReferrerSerialized());
	}

	/**
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
	 *
	 * @return array
	 */
	public function getConfig()
	{
		return $this->config;
	}
}
