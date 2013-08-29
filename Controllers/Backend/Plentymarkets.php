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
require_once PY_COMPONENTS . 'Import/PlentymarketsImportController.php';
require_once PY_COMPONENTS . 'Export/PlentymarketsExportController.php';
require_once PY_COMPONENTS . 'Mapping/PlentymarketsMappingController.php';

/**
 * This class is a main plentymarkets backend action controller. This controller processes all kinds of backend actions 
 * of the plentymarkets plugin like saving the settings or loading different kinds of data.
 *  
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class Shopware_Controllers_Backend_Plentymarkets extends Shopware_Controllers_Backend_ExtJs
{

	/**
	 * Loads the status of the continous data exchange
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
	 * Loads the settings
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
	 * Loads stores settings
	 */
	public function getSettingsStoresAction()
	{
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
				'warehouses' => array_values(PlentymarketsImportController::getWarehouseList()),
				'orderReferrer' => array_values(PlentymarketsImportController::getOrderReferrerList()),
				'orderStatus' => array_values($orderStatusList),
				'multishops' => array_values(PlentymarketsImportController::getStoreList()),
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
		$Config->setItemWarehouseID($this->Request()->ItemWarehouseID);
		$Config->setItemCleanupActionID($this->Request()->ItemCleanupActionID);
		$Config->setItemCategoryRootID($this->Request()->ItemCategoryRootID);
		$Config->setDefaultCustomerGroupKey($this->Request()->DefaultCustomerGroupKey);
		$Config->setItemWarehousePercentage($this->Request()->ItemWarehousePercentage);
		$Config->setItemProducerID($this->Request()->ItemProducerID);
		$Config->setOrderMarking1($this->Request()->OrderMarking1);
		$Config->setOrderReferrerID($this->Request()->OrderReferrerID);
		$Config->setOrderPaidStatusID($this->Request()->OrderPaidStatusID);
		$Config->setOutgoingItemsOrderStatus($this->Request()->OutgoingItemsOrderStatus);
		$Config->setOutgoingItemsID($this->Request()->OutgoingItemsID);
		$Config->setOutgoingItemsShopwareOrderStatusID($this->Request()->OutgoingItemsShopwareOrderStatusID);
		$Config->setIncomingPaymentShopwarePaymentFullStatusID($this->Request()->IncomingPaymentShopwarePaymentFullStatusID);
		$Config->setIncomingPaymentShopwarePaymentPartialStatusID($this->Request()->IncomingPaymentShopwarePaymentPartialStatusID);
		$Config->setStoreID($this->Request()->StoreID);

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
	 */
	public function saveMappingAction()
	{
		$params = $this->Request()->getParams();

		$entity = $params['entity'];

		$method = sprintf('delete%sByShopwareID', $entity);

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
	 * Announces an export
	 */
	public function handleExportAction()
	{
		$params = $this->Request()->getParams();

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
			
			$method = sprintf('set%sExportStatus', $params['ExportEntityName']);
			PlentymarketsConfig::getInstance()->$method('error');
			
			$method = sprintf('set%sExportLastErrorMessage', $params['ExportEntityName']);
			PlentymarketsConfig::getInstance()->$method($E->getMessage());

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
	 * Loads the export status list
	 */
	public function getExportStatusListAction()
	{
		$this->View()->assign(array(
			'success' => true,
			'data' => array_values($this->getExportStatusList())
		));
	}

	/**
	 * Loads the plenty mapping data
	 */
	public function getPlentyMappingDataAction()
	{
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

			case 'Vat':

				if ($forceReload)
				{
					PlentymarketsConfig::getInstance()->setMiscVatLastImport(0);
				}

				$data = PlentymarketsImportController::getVatList();
				break;

			case 'Referrer':
				$data = PlentymarketsImportController::getOrderReferrerList();
				break;

			case 'ShippingProfile':

				if ($forceReload)
				{
					PlentymarketsConfig::getInstance()->setMiscShippingProfilesLastImport(0);
				}

				$data = PlentymarketsImportController::getShippingProfileList();
				break;

			case 'MethodOfPayment':

				if ($forceReload)
				{
					PlentymarketsConfig::getInstance()->setMiscMethodsOfPaymentLastImport(0);
				}

				$data = PlentymarketsImportController::getMethodOfPaymentList();
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
	 * Loads the mapping data
	 */
	public function getMappingDataAction()
	{
		require_once PY_COMPONENTS . 'Mapping/PlentymarketsMappingDataController.php';

		$map = $this->Request()->getParam('map');
		$DataController = new PlentymarketsMappingDataController($this->Request()->getParam('auto', false));

		switch ($map)
		{
			case 'Vat':
				$rows = $DataController->getVat();
				break;

			case 'CustomerClass':
				$rows = $DataController->getCustomerClass();
				break;

			case 'MethodOfPayment':
				$rows = $DataController->getMethodOfPayment();
				break;

			case 'Referrer':
				$rows = $DataController->getReferrer();
				break;

			case 'ShippingProfile':
				$rows = $DataController->getShippingProfile();
				break;

			case 'Country':
				$rows = $DataController->getCountry();
				break;

			case 'Currency':
				$rows = $DataController->getCurrency();
				break;

			case 'MeasureUnit':
				$rows = $DataController->getMeasureUnit();
				break;
		}

		foreach ($rows as $position => &$row)
		{
			$row['position'] = $position;
		}

		$this->View()->assign(array(
			'success' => true,
			'data' => $rows,
			'total' => count($rows)
		));
	}

	/**
	 * Loads the log data
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
	 * Loads the status data
	 */
	public function getMappingStatusAction()
	{
		$this->View()->assign(array(
			'success' => true,
			'data' => array_values(PlentymarketsMappingController::getStatusList())
		));
	}

	/**
	 * Loads the warehouse list data
	 */
	public function getWarehouseListAction()
	{
		$this->View()->assign(array(
			'success' => true,
			'data' => array_values(PlentymarketsImportController::getWarehouseList())
		));
	}

	/**
	 * Loads the order status list data
	 */
	public function getOrderStatusListAction()
	{
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
	 * Loads the referrer list data
	 */
	public function getReferrerListAction()
	{
		$this->View()->assign(array(
			'success' => true,
			'data' => array_values(PlentymarketsImportController::getOrderReferrerList())
		));
	}

	/**
	 * Loads the multishop list data
	 */
	public function getMultishopListAction()
	{
		$this->View()->assign(array(
			'success' => true,
			'data' => array_values(PlentymarketsImportController::getStoreList())
		));
	}

	/**
	 * Loads the producer list data
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
	 * Checks the entered credentials data
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
