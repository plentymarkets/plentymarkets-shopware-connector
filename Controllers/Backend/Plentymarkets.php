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

require_once PY_COMPONENTS . 'Config/PlentymarketsConfig.php';
require_once PY_COMPONENTS . 'Utils/PlentymarketsLogger.php';
require_once PY_COMPONENTS . 'Utils/PlentymarketsUtils.php';
require_once PY_COMPONENTS . 'Soap/Client/PlentymarketsSoapClient.php';

/**
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class Shopware_Controllers_Backend_Plentymarkets extends Shopware_Controllers_Backend_ExtJs
{

	/**
	 * Returns the status of the continous data exchange
	 *
	 * @return array
	 */
	public function getDxContinuousAction()
	{
		$Config = PlentymarketsConfig::getInstance();

		$this->View()->assign(array(
			'success' => true,
			'data' => array(
				'ExportOrderStatus' => $Config->getExportOrderStatus(0),
				'ExportOrderError' => $Config->getExportOrderError(''),
				'ExportOrderLastRunTimestamp' => $Config->getExportOrderLastRunTimestamp(0),
				'ExportOrderNextRunTimestamp' => $Config->getExportOrderNextRunTimestamp(0),

				'ImportItemStatus' => $Config->getImportItemStatus(0),
				'ImportItemError' => $Config->getImportItemError(''),
				'ImportItemLastUpdateTimestamp' => $Config->getImportItemLastUpdateTimestamp(0),
				'ImportItemLastRunTimestamp' => $Config->getImportItemLastRunTimestamp(0),
				'ImportItemNextRunTimestamp' => $Config->getImportItemNextRunTimestamp(0),

				'ImportItemStockStatus' => $Config->getImportItemStockStatus(0),
				'ImportItemStockError' => $Config->getImportItemStockError(''),
				'ImportItemStockLastUpdateTimestamp' => $Config->getImportItemStockLastUpdateTimestamp(0),
				'ImportItemStockLastRunTimestamp' => $Config->getImportItemStockLastRunTimestamp(0),
				'ImportItemStockNextRunTimestamp' => $Config->getImportItemStockNextRunTimestamp(0),

				'ImportItemPriceStatus' => $Config->getImportItemPriceStatus(0),
				'ImportItemPriceError' => $Config->getImportItemPriceError(''),
				'ImportItemPriceLastRunTimestamp' => $Config->getImportItemPriceLastRunTimestamp(0),
				'ImportItemPriceNextRunTimestamp' => $Config->getImportItemPriceNextRunTimestamp(0)
			)
		));
	}

	/**
	 * Returns the settings
	 *
	 * @return array
	 */
	public function getSettingsListAction()
	{
		// Check the api, mapping and export status
		PlentymarketsUtils::checkDxStatus();

		$this->View()->assign(array(
			'success' => true,
			'data' => PlentymarketsConfig::getInstance()->getConfig()
		));
	}

	/**
	 */
	public function getSettingsStoresAction()
	{
		require_once PY_COMPONENTS . 'Import/PlentymarketsImportController.php';

		if ($this->Request()->get('refresh', false) == true)
		{
			PlentymarketsConfig::getInstance()->setMiscOrderStatusLastImport(0);
			PlentymarketsConfig::getInstance()->setMiscWarehousesLastImport(0);
			PlentymarketsConfig::getInstance()->setMiscSalesOrderReferrerLastImport(0);
			PlentymarketsConfig::getInstance()->setMiscMultishopsLastImport(0);
		}

		$orderStatusList = PlentymarketsImportController::getOrderStatusList();
		$orderStatusList[0] = array(
			'status' => 0,
			'name' => '---'
		);

		ksort($orderStatusList);

		$this->View()->assign(array(
			'success' => true,
			'data' => array(
				'warehouses' => array_values(PlentymarketsImportController::getWarehouses()),
				'orderReferrer' => array_values(PlentymarketsImportController::getOrderReferrerList()),
				'orderStatus' => array_values($orderStatusList),
				'multishops' => array_values(PlentymarketsImportController::getMultishops()),
				'producers' => Shopware()->Db()
					->fetchAll('
						SELECT id, name FROM s_articles_supplier ORDER BY name
					'),
				'categories' => Shopware()->Db()
					->fetchAll('
						SELECT id, description AS name FROM s_categories WHERE parent IS NOT NULL AND path IS NULL
					')
			)
		));
	}

	/**
	 * Saves the settings
	 */
	public function saveSettingsAction()
	{
		if ($this->Request()->get('check', false) == true)
		{
			// Check everything
			PlentymarketsUtils::checkDxStatus();

			$this->View()->assign(array(
				'success' => true,
				'data' => PlentymarketsConfig::getInstance()->getConfig()
			));

			return;
		}

		$Config = PlentymarketsConfig::getInstance();

		// Previous Hash
		$apiUserHash = $Config->getApiUserHash('');

		// API
		// Sanitize the Wsdl
		$wsdlParts = parse_url($this->Request()->ApiWsdl);
		$wsdl = sprintf('%s://%s', $wsdlParts['scheme'], $wsdlParts['host']);

		$Config->setApiWsdl($wsdl);
		$Config->setApiUsername($this->Request()->ApiUsername);
		$Config->setApiPassword($this->Request()->ApiPassword);

		$hash = md5($wsdl . $this->Request()->ApiUsername . $this->Request()->ApiPassword);
		if ($apiUserHash != $hash)
		{
			$Config->setApiUserHash($hash);
			$Config->setApiUserID(-1);
			$Config->setApiLastAuthTimestamp('');
			$Config->setApiToken('');

			// Check the connection
			PlentymarketsUtils::checkApiConnectionStatus();
		}

		// Item
		$Config->setItemWarehouseID((string) $this->Request()->ItemWarehouseID);
		$Config->setItemCategoryRootID((string) $this->Request()->ItemCategoryRootID);
		$Config->setDefaultCustomerGroupKey((string) $this->Request()->DefaultCustomerGroupKey);
		$Config->setItemWarehousePercentage((string) $this->Request()->ItemWarehousePercentage);
		$Config->setItemProducerID($this->Request()->ItemProducerID);
		$Config->setOrderMarking1($this->Request()->OrderMarking1);
		$Config->setOrderReferrerID((string) $this->Request()->OrderReferrerID);
		$Config->setOrderPaidStatusID($this->Request()->OrderPaidStatusID);
		$Config->setOutgoingItemsOrderStatus($this->Request()->OutgoingItemsOrderStatus);
		$Config->setOutgoingItemsID($this->Request()->OutgoingItemsID);
		$Config->setOutgoingItemsShopwareOrderStatusID($this->Request()->OutgoingItemsShopwareOrderStatusID);
		$Config->setIncomingPaymentShopwarePaymentFullStatusID($this->Request()->IncomingPaymentShopwarePaymentFullStatusID);
		$Config->setIncomingPaymentShopwarePaymentPartialStatusID($this->Request()->IncomingPaymentShopwarePaymentPartialStatusID);
		$Config->setWebstoreID((string) $this->Request()->WebstoreID);

		//
		if ($Config->getOutgoingItemsIntervalID() != $this->Request()->OutgoingItemsIntervalID)
		{
			switch ($this->Request()->OutgoingItemsIntervalID)
			{
				case 1:
					$nextrun = (date('H') > 12) ? strtotime('tomorrow noon') : strtotime('noon');
					$interval = 68400;
					break;

				case 2:
					$nextrun = (date('H') > 18) ? strtotime('tomorrow 6pm') : strtotime('6pm');
					$interval = 68400;
					break;
				case 3:
				default:
					$nextrun = strtotime(date('Y-m-d H:0', strtotime('+ 1 hour')));
					$interval = 60 * 60;
			}

			$Config->setOutgoingItemsIntervalID($this->Request()->OutgoingItemsIntervalID);

			Shopware()->Db()->query('
				UPDATE s_crontab
					SET
						next = FROM_UNIXTIME(' . $nextrun . '),
						`interval` = ' . $interval . '
					WHERE action = "Shopware_CronJob_PlentymarketsOrderImportCron"
			');
		}

		// Check dx status
		PlentymarketsUtils::checkDxStatus();

		// User settings of the data exchange
		$Config->setMayDatexUser((integer) ($this->Request()->MayDatexUser == true));

		// Activate the actual settings if the user
		// wants to exchange data and if he is allowed to
		if ($this->Request()->MayDatexUser == true && $Config->getMayDatex(0))
		{
			$Config->setMayDatexActual(1);
		}
		else
		{
			$Config->setMayDatexActual(0);
		}

		$this->View()->assign(array(
			'success' => true,
			'data' => $Config->getConfig()
		));
	}

	/**
	 * Saves one mapping row
	 *
	 * @return array
	 */
	public function saveMappingAction()
	{
		$params = $this->Request()->getParams();

		$entity = $params['entity'];

		$method = sprintf('delete%sByShopwareID', $entity);

		require_once PY_COMPONENTS . 'Mapping/PlentymarketsMappingController.php';

		// Delete the mapping for this shopware id
		call_user_func(array(
			'PlentymarketsMappingController',
			$method
		), $params['id']);

		$method = sprintf('add%s', $entity);

		call_user_func(array(
			'PlentymarketsMappingController',
			$method
		), $params['id'], $params['selectedPlentyId']);

		// Neu schreiben
		$this->View()->assign(array(
			'success' => true,
			'data' => PlentymarketsMappingController::getStatusByEntity($entity)
		));
	}

	/**
	 * Annonces an export
	 *
	 * @return array
	 */
	public function handleExportAction()
	{
		$params = $this->Request()->getParams();

		require_once PY_COMPONENTS . 'Export/PlentymarketsExportController.php';

		try
		{
			if ($params['ExportAction'] == 'restart')
			{

				PlentymarketsExportController::getInstance()->restart($params['ExportEntityName']);
				PlentymarketsLogger::getInstance()->message('Export:Initial:' . ucfirst($params['ExportEntityName']), 'Re-Announced');
			}
			else
			{
				PlentymarketsExportController::getInstance()->announce($params['ExportEntityName']);
				PlentymarketsLogger::getInstance()->message('Export:Initial:' . ucfirst($params['ExportEntityName']), 'Announced');
			}

			$success = true;
			$message = 'Export vorgemerkt';
		}
		catch (\Exception $E)
		{
			$success = false;
			$message = $E->getMessage();

			PlentymarketsLogger::getInstance()->error('Export:Initial:' . ucfirst($params['ExportEntityName']), 'Announcement failed: ' . $message);
		}

		$settings = $this->getExportStatusList();

		$this->View()->assign(array(
			'success' => $success,
			'message' => $message,
			'data' => $settings[$params['ExportEntityName']]
		));
	}

	/**
	 * Returns the status for each initial export entity
	 *
	 * @return array
	 */
	protected function getExportStatusList()
	{
		$entities = array(
			'ItemCategory',
			'ItemAttribute',
			'ItemProperty',
			'ItemProducer',
			'Item',
			'Customer',
		);

		$settings = array(
			'ExportStatus' => 'open',
			'ExportTimestampStart' => -1,
			'ExportTimestampFinished' => -1,
			'ExportLastErrorMessage' => '',
			'ExportQuantity' => -1
		);

		$Config = PlentymarketsConfig::getInstance();

		foreach ($entities as $position => $entity)
		{

			$data = array(
				'position' => $position,
				'ExportEntityName' => $entity,
				'ExportEntityDescription' => $entity
			);

			foreach ($settings as $setting => $default)
			{
				$method = sprintf('get%s%s', $entity, $setting);
				$data[$setting] = $Config->$method($default);
			}

			$export[$entity] = $data;
		}

		return $export;
	}

	/**
	 */
	public function getExportStatusListAction()
	{
		$this->View()->assign(array(
			'success' => true,
			'data' => array_values($this->getExportStatusList())
		));
	}

	/**
	 */
	public function getPlentyMappingDataAction()
	{
		require_once PY_COMPONENTS . 'Import/PlentymarketsImportController.php';

		$forceReload = $this->Request()->get('force', false);

		switch ($this->Request()->map)
		{
			case 'Country':
				$data = PlentymarketsConfig::getInstance()->getMiscCountriesSorted();
				break;

			case 'Currency':
				$data = PlentymarketsConfig::getInstance()->getMiscCurrenciesSorted();
				break;

			case 'MeasureUnit':
				$data = PlentymarketsConfig::getInstance()->getItemMeasureUnits();
				break;

			case 'VAT':

				if ($forceReload)
				{
					PlentymarketsConfig::getInstance()->setMiscVatLastImport(0);
				}

				$data = PlentymarketsImportController::getVat();
				break;

			case 'ShippingProfile':

				if ($forceReload)
				{
					PlentymarketsConfig::getInstance()->setMiscShippingProfilesLastImport(0);
				}

				$data = PlentymarketsImportController::getShippingProfiles();
				break;

			case 'MethodOfPayment':

				if ($forceReload)
				{
					PlentymarketsConfig::getInstance()->setMiscMethodsOfPaymentLastImport(0);
				}

				$data = PlentymarketsImportController::getMethodsOfPayment();
				break;

			case 'CustomerClass':

				if ($forceReload)
				{
					PlentymarketsConfig::getInstance()->setMiscCustomerClassLastImport(0);
				}

				$data = PlentymarketsImportController::getCustomerClassList();
				break;
		}

		$this->View()->assign(array(
			'success' => true,
			'data' => array_values($data)
		));
	}

	/**
	 */
	public function getMappingDataAction()
	{
		require_once PY_COMPONENTS . 'Import/PlentymarketsImportController.php';
		require_once PY_COMPONENTS . 'Mapping/PlentymarketsMappingController.php';

		$map = $this->Request()->getParam('map');

		switch ($map)
		{
			case 'VAT':
				// s_core_tax
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

				break;

			case 'CustomerClass':

				// s_core_tax
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
// 					if ($row['plentyID'] >= 0)
// 					{
						$row['plentyName'] = $plentyVat[$row['plentyID']]['name'];
// 					}
				}

				break;

			case 'MethodOfPayment':
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

				$plentyShipping = PlentymarketsImportController::getMethodsOfPayment();
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

				break;

			case 'ShippingProfile':
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

				$plentyShipping = PlentymarketsImportController::getShippingProfiles();

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

				break;

			case 'Country':
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

				break;

			case 'Currency':
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

				break;

			case 'MeasureUnit':
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

				break;
		}

		foreach ($rows as $position => &$row)
		{
			$row["position"] = $position;
		}

		$this->View()->assign(array(
			'success' => true,
			'data' => $rows,
			'total' => count($rows)
		));
	}

	/**
	 */
	public function getLogAction()
	{
		$data = PlentymarketsLogger::getInstance()->get($this->Request()
			->get('start', 0), $this->Request()
			->get('limit', 50), $this->Request()
			->get('type', 0));

		$data['success'] = true;

		$this->View()->assign($data);
	}

	/**
	 */
	public function getMappingStatusAction()
	{
		require_once PY_COMPONENTS . 'Mapping/PlentymarketsMappingController.php';
		$this->View()->assign(array(
			'success' => true,
			'data' => array_values(PlentymarketsMappingController::getStatusList())
		));
	}

	/**
	 */
	public function getWarehouseListAction()
	{
		require_once PY_COMPONENTS . 'Import/PlentymarketsImportController.php';

		$this->View()->assign(array(
			'success' => true,
			'data' => array_values(PlentymarketsImportController::getWarehouses())
		));
	}

	/**
	 */
	public function getOrderStatusListAction()
	{
		require_once PY_COMPONENTS . 'Import/PlentymarketsImportController.php';

		$values = PlentymarketsImportController::getOrderStatusList();
		$values[0] = array(
			'status' => 0,
			'name' => '---'
		);

		ksort($values);

		$this->View()->assign(array(
			'success' => true,
			'data' => array_values($values)
		));
	}

	/**
	 */
	public function getReferrerListAction()
	{
		$this->View()->assign(array(
			'success' => true,
			'data' => array_values(PlentymarketsImportController::getOrderReferrerList())
		));
	}

	/**
	 */
	public function getMultishopListAction()
	{
		require_once PY_COMPONENTS . 'Import/PlentymarketsImportController.php';

		$this->View()->assign(array(
			'success' => true,
			'data' => array_values(PlentymarketsImportController::getMultishops())
		));
	}

	/**
	 */
	public function getProducerListAction()
	{
		$this->View()->assign(array(
			'success' => true,
			'data' => Shopware()->Db()
				->fetchAll('
				SELECT id, name FROM s_articles_supplier ORDER BY name
			')
		));
	}

	/**
	 */
	public function testApiCredentialsAction()
	{
		try
		{
			$wsdl = $this->Request()->ApiWsdl . '/plenty/api/soap/version110/?xml';
			$Client = PlentymarketsSoapClient::getTestInstance($wsdl, $this->Request()->ApiUsername, $this->Request()->ApiPassword);
		}
		catch (Exception $E)
		{
			$this->View()->assign(array(
				'success' => false,
				'message' => $E->getMessage()
			));
			return;
		}

		$this->View()->assign(array(
			'success' => true,
			'data' => $Client->GetServerTime()
		));
	}
}
