<?php
require_once PY_COMPONENTS . 'Import/PlentymarketsImportController.php';
require_once PY_COMPONENTS . 'Mapping/PlentymarketsMappingController.php';

class PlentymarketsMappingDataController
{

	public static function getCountry()
	{
		$rows = Shopware()->Db()
			->query('
					SELECT
							C.id, C.countryname name,
							IFNULL(PMC.plentyID, 0) plentyID
						FROM s_core_countries C
						LEFT JOIN plenty_mapping_country PMC
							ON PMC.shopwareID = C.id
						ORDER BY C.countryname
				')
			->fetchAll();

		$plentyCountries = PlentymarketsConfig::getInstance()->getMiscCountries();

		foreach ($rows as &$row)
		{
			if ($row['plentyID'])
			{
				$row['plentyName'] = $plentyCountries[$row['plentyID']]['name'];
			}
			else if ($this->Request()->get('auto', false))
			{
				foreach ($plentyCountries as $plentyData)
				{
					$distance = levenshtein($row['name'], $plentyData['name']);
					if ($distance <= 2 || strstr($plentyData['name'], $row['name']))
					{
						$row['plentyName'] = $plentyData['name'];
						$row['plentyID'] = $plentyData['id'];
						PlentymarketsMappingController::addCountry($row['id'], $plentyData['id']);

						if ($distance == 0)
						{
							break;
						}
					}
				}
			}
			else
			{
				$row['plentyName'] = '';
			}
		}

		return $rows;
	}

	public static function getCurrency()
	{
		$rows = Shopware()->Db()
			->query('
					SELECT
							C.currency id, C.name,
							IFNULL(PMC.plentyID, 0) plentyID
						FROM s_core_currencies C
						LEFT JOIN plenty_mapping_currency PMC
							ON PMC.shopwareID = C.currency
						ORDER BY C.name
				')
			->fetchAll();

		$plentyCurrencies = PlentymarketsConfig::getInstance()->getMiscCurrenciesSorted();

		foreach ($rows as &$row)
		{
			if ($row['plentyID'])
			{
				$row['plentyName'] = $plentyCurrencies[$row['plentyID']]['name'];
			}
			else if ($this->Request()->get('auto', false))
			{
				foreach ($plentyCurrencies as $plentyData)
				{
					$distance = levenshtein($row['id'], $plentyData['name']);
					if ($distance == 0)
					{
						$row['plentyName'] = $plentyData['name'];
						$row['plentyID'] = $plentyData['id'];
						PlentymarketsMappingController::addCurrency($row['id'], $plentyData['id']);
					}
				}
			}
			else
			{
				$row['plentyName'] = '';
			}
		}

		return $rows;
	}

	public static function getCustomerClass()
	{
		$rows = Shopware()->Db()
			->query('
					SELECT
							C.id, description AS name,
							IFNULL(PMC.plentyID, -1) plentyID
						FROM s_core_customergroups C
						LEFT JOIN plenty_mapping_customer_class PMC
							ON PMC.shopwareID = C.id
						ORDER BY C.tax
				')
			->fetchAll();

		$plentyVat = PlentymarketsImportController::getCustomerClassList();
		foreach ($rows as &$row)
		{
			if (isset($plentyVat[$row['plentyID']]))
			{
				$row['plentyName'] = $plentyVat[$row['plentyID']]['name'];
			}
		}
		return $rows;
	}

	public static function getMeasureUnit()
	{
		$rows = Shopware()->Db()
			->query('
					SELECT
							C.id, CONCAT(C.description, " (", C.unit, ")") name,
							IFNULL(PMC.plentyID, 0) plentyID
						FROM s_core_units C
						LEFT JOIN plenty_mapping_measure_unit PMC
							ON PMC.shopwareID = C.id
						ORDER BY C.description
				')
			->fetchAll();

		$plentyMU = PlentymarketsConfig::getInstance()->getItemMeasureUnits();

		foreach ($rows as &$row)
		{
			$row['plentyName'] = $plentyMU[$row['plentyID']]['name'];
		}

		return $rows;
	}

	public static function getMethodOfPayment()
	{
		// s_core_tax
		$rows = Shopware()->Db()
			->query('
					SELECT
							C.id, C.description name,
							IFNULL(PMC.plentyID, -1) plentyID
						FROM s_core_paymentmeans C
						LEFT JOIN plenty_mapping_method_of_payment PMC
							ON PMC.shopwareID = C.id
						ORDER BY C.name
				')
			->fetchAll();

		$plentyShipping = PlentymarketsImportController::getMethodOfPaymentList();
		foreach ($rows as &$row)
		{
			if ($row['plentyID'] >= 0)
			{
				$row['plentyName'] = $plentyShipping[$row['plentyID']]['name'];
			}
			else if ($this->Request()->get('auto', false))
			{
				foreach ($plentyShipping as $plentyData)
				{
					$distance = levenshtein($row['name'], $plentyData['name']);
					if ($distance <= 2 || strstr($plentyData['name'], $row['name']))
					{
						$row['plentyName'] = $plentyData['name'];
						$row['plentyID'] = $plentyData['id'];
						PlentymarketsMappingController::addMethodOfPayment($row['id'], $plentyData['id']);

						if ($distance == 0)
						{
							break;
						}
					}
				}
			}
			else
			{
				$row['plentyName'] = '';
			}
		}

		return $rows;
	}

	public static function getShippingProfile()
	{
		// s_core_tax
		$rows = Shopware()->Db()
			->query('
					SELECT
							C.id, C.name name,
							IFNULL(PMC.plentyID, 0) plentyID
						FROM s_premium_dispatch C
						LEFT JOIN plenty_mapping_shipping_profile PMC
							ON PMC.shopwareID = C.id
						ORDER BY C.name
				')
			->fetchAll();

		$plentyShipping = PlentymarketsImportController::getShippingProfileList();

		foreach ($rows as &$row)
		{
			if ($row['plentyID'])
			{
				$row['plentyName'] = $plentyShipping[$row['plentyID']]['name'];
			}
			else if ($this->Request()->get('auto', false))
			{
				foreach ($plentyShipping as $plentyData)
				{
					$distance = levenshtein($row['name'], $plentyData['name']);
					if ($distance <= 2 || strstr($plentyData['name'], $row['name']))
					{
						$row['plentyName'] = $plentyData['name'];
						$row['plentyID'] = $plentyData['id'];
						PlentymarketsMappingController::addShippingProfile($row['id'], $plentyData['id']);

						if ($distance == 0)
						{
							break;
						}
					}
				}
			}
			else
			{
				$row['plentyName'] = '';
			}
		}

		return $rows;
	}

	public static function getVat()
	{
		$rows = Shopware()->Db()
			->query('
					SELECT
							C.id, CONCAT(C.tax, " %") name,
							IFNULL(PMC.plentyID, -1) plentyID
						FROM s_core_tax C
						LEFT JOIN plenty_mapping_vat PMC
							ON PMC.shopwareID = C.id
						ORDER BY C.tax
				')
			->fetchAll();

		$plentyVat = PlentymarketsImportController::getVat();
		foreach ($rows as &$row)
		{
			if ($row['plentyID'] >= 0)
			{
				$row['plentyName'] = $plentyVat[$row['plentyID']]['name'];
			}
		}

		return $rows;
	}
}
