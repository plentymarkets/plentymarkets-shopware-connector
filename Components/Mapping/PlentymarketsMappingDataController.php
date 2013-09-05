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

require_once PY_COMPONENTS . 'Import/PlentymarketsImportController.php';
require_once PY_COMPONENTS . 'Mapping/PlentymarketsMappingController.php';

/**
 * The PlentymarketsMappingDataController is the main controller for mapping issues. This class is called
 * in the class Shopware_Controllers_Backend_Plentymarkets and it uses the class PlentymarketsMappingController
 * for different data operations. This class returns the different mapping data.
 * 
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsMappingDataController
{
	/**
	 *
	 * @var bool
	 */
	protected $auto = false;

	/**
	 *
	 * @param auto $auto
	 */
	public function __construct($auto)
	{
		$this->auto = $auto;
	}

	public function getCountry()
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
			else if ($this->auto)
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

	public function getCurrency()
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
			else if ($this->auto)
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

	public function getCustomerClass()
	{
		$rows = Shopware()->Db()
			->query('
					SELECT
							C.id, description AS name,
							IFNULL(PMC.plentyID, -99) plentyID
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

	public function getMeasureUnit()
	{
		$rows = Shopware()->Db()
			->query('
					SELECT
							C.id, CONCAT(C.description, " (", C.unit, ")") name,
							C.description, C.unit,
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
			if ($row['plentyID'])
			{
				$row['plentyName'] = $plentyMU[$row['plentyID']]['name'];
			}
			else if ($this->auto)
			{
				$plentyUnits = PlentymarketsConfig::getInstance()->getItemMeasureUnits();
				foreach ($plentyUnits as $plentyData)
				{
					preg_match('/(.*?) \((.*?)\)/', $plentyData['name'], $matches);
					
					if (!is_array($matches))
					{
						$name = $plentyData['name'];
						$unit = '';
					}
					else
					{
						list($match, $name, $unit) = $matches;
					}
					
					$distance = levenshtein($row['description'], $name);
					
					if ($row['unit'] == $unit || $distance <= 2)
					{
						$row['plentyName'] = $plentyData['name'];
						$row['plentyID'] = $plentyData['id'];
						PlentymarketsMappingController::addMeasureUnit($row['id'], $plentyData['id']);
						break;
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

	public function getMethodOfPayment()
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
						WHERE active = 1
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
			else if ($this->auto)
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

	public function getReferrer()
	{
		// s_core_tax
		$rows = Shopware()->Db()
			->query('
					SELECT
							C.id, C.company name,
							IFNULL(PMC.plentyID, 0) plentyID
						FROM s_emarketing_partner C
						LEFT JOIN plenty_mapping_referrer PMC
							ON PMC.shopwareID = C.id
						WHERE active = 1
						ORDER BY C.company
			')
			->fetchAll();

		$referrer = PlentymarketsImportController::getOrderReferrerList();
		
		foreach ($rows as &$row)
		{
			if ($row['plentyID'])
			{
				$row['plentyName'] = $referrer[$row['plentyID']]['name'];
			}
			else if ($this->auto)
			{
				foreach ($referrer as $plentyData)
				{
					$distance = levenshtein($row['name'], $plentyData['name']);
					if ($distance <= 2 || strstr($plentyData['name'], $row['name']))
					{
						$row['plentyName'] = $plentyData['name'];
						$row['plentyID'] = $plentyData['id'];
						PlentymarketsMappingController::addReferrer($row['id'], $plentyData['id']);

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

	public function getShippingProfile()
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
						WHERE active = 1
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
			else if ($this->auto)
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

	public function getVat()
	{
		$rows = Shopware()->Db()
			->query('
					SELECT
							C.id, CONCAT(C.tax, " %") name,
							C.tax,
							IFNULL(PMC.plentyID, -1) plentyID
						FROM s_core_tax C
						LEFT JOIN plenty_mapping_vat PMC
							ON PMC.shopwareID = C.id
						ORDER BY C.tax
				')
			->fetchAll();

		$plentyVat = PlentymarketsImportController::getVatList();
		foreach ($rows as &$row)
		{
			if ($row['plentyID'] >= 0)
			{
				$row['plentyName'] = $plentyVat[$row['plentyID']]['name'];
			}
			else if ($this->auto)
			{
				$plentyVat = PlentymarketsImportController::getVatList();
				foreach ($plentyVat as $plentyData)
				{
					list($name, $percent) = explode(' ', $plentyData['name']);
					if ((float) $row['tax'] == (float) $name)
					{
						$row['plentyName'] = $plentyData['name'];
						$row['plentyID'] = $plentyData['id'];
						PlentymarketsMappingController::addVat($row['id'], $plentyData['id']);
						break;
					}
				}
			}
		}
		
		return $rows;
	}
	
	public function getShops()
	{
		$rows = Shopware()->Db()
			->query('
					SELECT
							C.id, C.name, C.`default`,
							IFNULL(PMC.plentyID, -1) plentyID
						FROM s_core_shops C
						LEFT JOIN plenty_mapping_shop PMC
							ON PMC.shopwareID = C.id
						WHERE active = 1
						ORDER BY C.name
				')
			->fetchAll();

		$plentyStores = PlentymarketsImportController::getStoreList();
		foreach ($rows as &$row)
		{
			if ($row['plentyID'] >= 0)
			{
				$row['plentyName'] = $plentyStores[$row['plentyID']]['name'];
			}
			else if ($this->auto)
			{
// 				foreach ($plentyStores as $plentyData)
// 				{
// 					list($name, $percent) = explode(' ', $plentyData['name']);
// 					if ((float) $row['tax'] == (float) $name)
// 					{
// 						$row['plentyName'] = $plentyData['name'];
// 						$row['plentyID'] = $plentyData['id'];
// 						PlentymarketsMappingController::addVat($row['id'], $plentyData['id']);
// 						break;
// 					}
// 				}
			}
			
			if ($row['default'])
			{
				$row['name'] .= ' (Standard)';
			}
		}
		
		return $rows;
	}
}
