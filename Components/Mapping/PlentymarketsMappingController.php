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
 * The PlentymarketsMappingController does the actual data mapping. Therefore it uses the corresponding
 * mapping entity classes like PlentymarketsMappingEntityCustomer in /Mapping/Entity. This class is called
 * in the most classes of the plentymarkets plugin.
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsMappingController
{

	/**
	 * Dispatches a method call to the actual mapping class
	 *
	 * @param string $name
	 * @param array $arguments
	 * @throws Exception
	 * @return mixed
	 */
	public static function __callStatic($name, $arguments)
	{
		$matches = array();
		preg_match('/(add|get|delete)([A-Z][a-zA-Z]*?)(?:By(Shopware|Plenty)ID)?$/', $name, $matches);

		if (count($matches) === 3 && $matches[1] === 'add')
		{
			$method = 'add';
		}
		else if (count($matches) === 4)
		{
			if ($matches[1] === 'get')
			{
				$method = sprintf('getBy%sID', $matches[3]);
			}
			else if ($matches[1] === 'delete')
			{
				$method = sprintf('deleteBy%sID', $matches[3]);
			}
			else
			{
				throw new \Exception();
			}
		}
		else
		{
			throw new \Exception();
		}

		//
		$classname = sprintf('PlentymarketsMappingEntity%s', $matches[2]);

		//

		//
		$Instance = $classname::getInstance();

		//
		return call_user_func_array(array(
			$Instance,
			$method
		), $arguments);
	}

	/**
	 * Returns the status of each entity
	 *
	 * @return array
	 */
	public static function getStatusList()
	{
		$resources = array(
			'Currency' => array(
				's_core_currencies',
				'currency',
				'currency'
			),
			'MeasureUnit' => array(
				's_core_units',
				'measure_unit'
			),
			'MethodOfPayment' => array(
				's_core_paymentmeans',
				'method_of_payment'
			),
			'Vat' => array(
				's_core_tax',
				'vat'
			),
			'CustomerClass' => array(
				's_core_customergroups',
				'customer_class'
			),
			'ShippingProfile' => array(
				's_premium_dispatch',
				'shipping_profile'
			),
			'Country' => array(
				's_core_countries',
				'country'
			),
			'Shop' => array(
				's_core_shops',
				'shop'
			)
		);

		$status = array();

		foreach ($resources as $resource => $tables)
		{

			$id = isset($tables[2]) ? $tables[2] : 'id';
			
			$whereActive = '';
			if($tables[0] == 's_core_paymentmeans' ||
				$tables[0] == 's_premium_dispatch' ||
				$tables[0] == 's_core_shops')
			{
				$whereActive = ' AND active = 1';
			}
			
			$Statement = Shopware()->Db()->prepare('
				SELECT
						COUNT(*) open
					FROM ' . $tables[0] . '
					WHERE ' . $id . ' NOT IN (
						SELECT shopwareId
						FROM plenty_mapping_' . $tables[1].'
					)'.$whereActive.';'
			);

			try {
				$Statement->execute();
	
				$status[$resource] = array(
					'name' => $resource,
					'open' => (integer) $Statement->fetchObject()->open
				);
			}
			catch (Exception $E)
			{
				$status[$resource] = array(
					'name' => $resource,
					'open' => 1
				);
			}
		}

		// static since the mapping is not mandatory
		$status['Referrer'] = array(
			'name' => 'Referrer',
			'open' => 0
		);
		
		return $status;
	}

	/**
	 * Returns the status of a single entity
	 *
	 * @param string $entity
	 * @return array
	 */
	public static function getStatusByEntity($entity)
	{
		$status = self::getStatusList();
		return $status[$entity];
	}

	/**
	 * Checks whether the mapping is complete
	 *
	 * @return boolean
	 */
	public static function isComplete()
	{
		foreach (self::getStatusList() as $resource)
		{
			if ($resource['open'] > 0)
			{
				return false;
			}
		}
		return true;
	}
}
