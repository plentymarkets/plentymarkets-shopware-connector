<?php

/**
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsMappingController
{

	/**
	 *
	 * @param string $name
	 * @param array $arguments
	 * @throws Exception
	 * @return mixed
	 */
	public function __callStatic($name, $arguments)
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
				throw new Exception();
			}
		}
		else
		{
			throw new Exception();
		}

		//
		$classname = sprintf('PlentymarketsMappingEntity%s', $matches[2]);

		//
		require_once __DIR__ . '/Entity/' . $classname . '.php';

		//
		$Instance = $classname::getInstance();

		//
		return call_user_func_array(array(
			$Instance,
			$method
		), $arguments);
	}

	/**
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
			'VAT' => array(
				's_core_tax',
				'vat'
			),
			'ShippingProfile' => array(
				's_premium_dispatch',
				'shipping_profile'
			),
			'Country' => array(
				's_core_countries',
				'country'
			)
		);

		$status = array();

		foreach ($resources as $resource => $tables)
		{

			$id = isset($tables[2]) ? $tables[2] : 'id';

			$Statement = Shopware()->Db()->prepare('
				SELECT
						COUNT(*) open
					FROM ' . $tables[0] . '
					WHERE ' . $id . ' NOT IN (
						SELECT shopwareId
						FROM plenty_mapping_' . $tables[1] . '
					);
			');

			$Statement->execute();

			$status[$resource] = array(
				'name' => $resource,
				'open' => (integer) $Statement->fetchObject()->open
			);
		}

		return $status;
	}

	/**
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
