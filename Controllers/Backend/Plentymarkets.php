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
 * This class is a main plentymarkets backend action controller. This controller processes all kinds of backend actions
 * of the plentymarkets plugin like saving the settings or loading different kinds of data.
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class Shopware_Controllers_Backend_Plentymarkets extends Shopware_Controllers_Backend_ExtJs
{
	/**
	 * Runs an cleanup action
	 */
	public function runCleanupActionAction()
	{

		switch ($this->Request()->get('entity'))
		{
			case PlentymarketsGarbageCollector::ACTION_PROPERTIES:
				PlentymarketsGarbageCollector::getInstance()->run(
					PlentymarketsGarbageCollector::ACTION_PROPERTIES
				);
				break;

			case PlentymarketsGarbageCollector::ACTION_MAPPING:
				PlentymarketsGarbageCollector::getInstance()->run(
					PlentymarketsGarbageCollector::ACTION_MAPPING
				);
				break;

		}

		$this->View()->assign(array(
			'success' => true
		));
	}


	/**
	 * Deleted a page of corrupt data
	 */
	public function deleteDataIntegrityInvalidDataAction()
	{

		$Check = PlentymarketsDataIntegrityController::getInstance()->getCheck($this->Request()->get('type'));
		$Check->deleteInvalidData(
			$this->Request()->get('start'),
			$this->Request()->get('limit')
		);

		// Cleanup
		PlentymarketsGarbageCollector::getInstance()->run(
			PlentymarketsGarbageCollector::ACTION_MAPPING
		);

		$this->View()->assign(array(
			'success' => true
		));
	}

	/**
	 * Returns the list of integrity checks
	 */
	public function getDataIntegrityInvalidListAction()
	{

		$Checks = PlentymarketsDataIntegrityController::getInstance()->getChecks();
		$data = array();
		foreach ($Checks as $Check)
		{
			$data[] = array(
				'name' => $Check->getName(),
				'fields' => $Check->getFields()
			);
		}

		$this->View()->assign(array(
			'success' => true,
			'data' => $data
		));
	}

	/**
	 * Returns a list of invalid data records
	 */
	public function getDataIntegrityInvalidDataListAction()
	{

		$Check = PlentymarketsDataIntegrityController::getInstance()->getCheck($this->Request()->get('type'));

		$this->View()->assign(array(
			'success' => true,
			'data' => $Check->getInvalidData(
				$this->Request()->get('start'),
				$this->Request()->get('limit')
			),
			'total' => $Check->getTotal()
		));
	}

	/**
	 * Loads the status of the continous data exchange
	 */
	public function getDxContinuousAction()
	{
		$Config = PlentymarketsConfig::getInstance();

		$this->View()->assign(array(
			'success' => true,
			'data' => array(

				// Export
				'export' => array(
					array(
						'Entity' => 'Order',
						'Section' => 'Order',
						'Status' => $Config->getExportOrderStatus(0),
						'Error' => htmlspecialchars($Config->getExportOrderError('')),
						'LastRunTimestamp' => $Config->getExportOrderLastRunTimestamp(0),
						'NextRunTimestamp' => $Config->getExportOrderNextRunTimestamp(0),
					),
					array(
						'Entity' => 'OrderIncomingPayment',
						'Section' => 'Order',
						'Status' => $Config->getExportOrderIncomingPaymentStatus(0),
						'Error' => htmlspecialchars($Config->getExportOrderIncomingPaymentError('')),
						'LastRunTimestamp' => $Config->getExportOrderIncomingPaymentLastRunTimestamp(0),
						'NextRunTimestamp' => $Config->getExportOrderIncomingPaymentNextRunTimestamp(0),
					)
				),

				// Import
				'import' => array(
					array(
						'Entity' => 'ItemStack',
						'Section' => 'Item',
						'Status' => $Config->getImportItemStackStatus(0),
						'Error' => htmlspecialchars($Config->getImportItemStackError('')),
						'LastRunTimestamp' => $Config->getImportItemStackLastRunTimestamp(0),
						'NextRunTimestamp' => $Config->getImportItemStackNextRunTimestamp(0),
					),
					array(
						'Entity' => 'Item',
						'Section' => 'Item',
						'Status' => $Config->getImportItemStatus(0),
						'Error' => htmlspecialchars($Config->getImportItemError('')),
						'LastRunTimestamp' => $Config->getImportItemLastRunTimestamp(0),
						'NextRunTimestamp' => $Config->getImportItemNextRunTimestamp(0),
					),
					array(
						'Entity' => 'ItemBundle',
						'Section' => 'Item',
						'Status' => $Config->getImportItemBundleStatus(0),
						'Error' => htmlspecialchars($Config->getImportItemBundleError('')),
						'LastRunTimestamp' => $Config->getImportItemBundleLastRunTimestamp(0),
						'NextRunTimestamp' => $Config->getImportItemBundleNextRunTimestamp(0),
					),
					array(
						'Entity' => 'ItemStock',
						'Section' => 'Item',
						'Status' => $Config->getImportItemStockStatus(0),
						'Error' => htmlspecialchars($Config->getImportItemStockError('')),
						'LastRunTimestamp' => $Config->getImportItemStockLastRunTimestamp(0),
						'NextRunTimestamp' => $Config->getImportItemStockNextRunTimestamp(0),
					),
					array(
						'Entity' => 'ItemPrice',
						'Section' => 'Item',
						'Status' => $Config->getImportItemPriceStatus(0),
						'Error' => htmlspecialchars($Config->getImportItemPriceError('')),
						'LastRunTimestamp' => $Config->getImportItemPriceLastRunTimestamp(0),
						'NextRunTimestamp' => $Config->getImportItemPriceNextRunTimestamp(0)
					),
					array(
						'Entity' => 'OrderIncomingPayment',
						'Section' => 'Order',
						'Status' => $Config->getImportOrderStatus(0),
						'Error' => htmlspecialchars($Config->getImportOrderError('')),
						'LastRunTimestamp' => $Config->getImportOrderLastRunTimestamp(0),
						'NextRunTimestamp' => $Config->getImportOrderNextRunTimestamp(0),
					),
					array(
						'Entity' => 'OrderOutgoingItems',
						'Section' => 'Order',
						'Status' => $Config->getImportOrderStatus(0),
						'Error' => htmlspecialchars($Config->getImportOrderError('')),
						'LastRunTimestamp' => $Config->getImportOrderLastRunTimestamp(0),
						'NextRunTimestamp' => $Config->getImportOrderNextRunTimestamp(0),
					)
				)
			)
		));
	}

	/**
	 * Returns the export wizard information
	 */
	public function getDxWizardAction()
	{
		$Wizard = PlentymarketsExportWizard::getInstance();

		$this->View()->assign(array(
			'success' => true,
			'data' => array(
				'isActive' => $Wizard->isActive(),
				'mayActivate' => $Wizard->mayActivate()
			)
		));
	}

	/**
	 * Activates or deativated the wizard
	 */
	public function setDxWizardAction()
	{
		$Wizard = PlentymarketsExportWizard::getInstance();

		if ($this->Request()->get('activate', 'no') == 'yes')
		{
			if ($Wizard->mayActivate())
			{
				$Wizard->activate();
			}
		}
		else
		{
			$Wizard->deactivate();
		}

		$this->View()->assign(array(
			'success' => true,
			'data' => array(
				'isActive' => $Wizard->isActive(),
				'mayActivate' => $Wizard->mayActivate()
			)
		));
	}

	/**
	 * Loads the settings
	 */
	public function getSettingsListAction()
	{
		// Check the api, mapping and export status
		PlentymarketsStatus::getInstance()->maySynchronize(false);

		$config = PlentymarketsConfig::getInstance()->getConfig();

		$config['_WebserverSoftware'] = $_SERVER['SERVER_SOFTWARE'];
		$config['_WebserverSignature'] = $_SERVER['SERVER_SIGNATURE'];
		$config['_PhpInterface'] = $_SERVER['GATEWAY_INTERFACE'];
		$config['_PhpVersion'] = PHP_VERSION;
		$config['_PhpMemoryLimit'] = ini_get('memory_limit');

		if (function_exists('apache_get_modules'))
		{
			$modules = apache_get_modules();
			$phpModules = array_filter($modules, function ($item)
			{
				return preg_match('/php|cgi/', $item);
			});
			sort($phpModules);
			$config['_ApacheModules'] = join('/', $phpModules);
		}
		else
		{
			$config['_ApacheModules'] = '';
		}

		if (isset($config['OrderShopgateMOPIDs']))
		{
			$orderShopgateMOPIDs = explode('|', $config['OrderShopgateMOPIDs']);
			$config['OrderShopgateMOPIDs'] = array_map('intval', $orderShopgateMOPIDs);
		}
		else
		{
			$config['OrderShopgateMOPIDs'] = array();
		}

		$this->View()->assign(array(
			'success' => true,
			'data' => $config
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
				'producers' => Shopware()->Db()
					->fetchAll('
						SELECT id, name FROM s_articles_supplier ORDER BY name
					'),
				'payments' => Shopware()->Db()
					->fetchAll('
					SELECT id, description as name FROM s_core_paymentmeans WHERE active = 0 ORDER BY name
				')
			)
		));
	}

	/**
	 * Saves the settings
	 */
	public function saveSettingsAction()
	{
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
		$Config->setApiIgnoreGetServerTime((integer) $this->Request()->ApiIgnoreGetServerTime);
		$Config->setApiUseGzipCompression((integer) $this->Request()->ApiUseGzipCompression);
		$Config->setApiLogHttpHeaders((integer) $this->Request()->ApiLogHttpHeaders);
		$Config->setApiHideCallsInLog((integer) $this->Request()->ApiHideCallsInLog);

		$hash = md5($wsdl . $this->Request()->ApiUsername . $this->Request()->ApiPassword);
		if ($apiUserHash != $hash)
		{
			$Config->setApiUserHash($hash);
			$Config->setApiUserID(-1);
			$Config->setApiLastAuthTimestamp('');
			$Config->setApiToken('');

			// Check the connection
			PlentymarketsStatus::getInstance()->isConnected();
		}

		// Item
		$Config->setItemWarehouseID($this->Request()->ItemWarehouseID);
		$Config->setItemCleanupActionID($this->Request()->ItemCleanupActionID);
		$Config->setItemCategoryRootID($this->Request()->ItemCategoryRootID);
		$Config->setItemImageSyncActionID($this->Request()->ItemImageSyncActionID == true ? IMPORT_ITEM_IMAGE_SYNC : IMPORT_ITEM_IMAGE_NO_SYNC);
		$Config->setItemCategorySyncActionID($this->Request()->ItemCategorySyncActionID == true ? IMPORT_ITEM_CATEGORY_SYNC : IMPORT_ITEM_CATEGORY_NO_SYNC);
		$Config->setItemNumberImportActionID($this->Request()->ItemNumberImportActionID == true ? IMPORT_ITEM_NUMBER : IMPORT_ITEM_NUMBER_NO);
		$Config->setItemBundleHeadActionID($this->Request()->ItemBundleHeadActionID == true ? IMPORT_ITEM_BUNDLE_HEAD : IMPORT_ITEM_BUNDLE_HEAD_NO);
		$Config->setItemAssociateImportActionID(
			$this->Request()->ItemAssociateImportActionID == PlentymarketsImportItemAssociateController::ACTION_DETACHED
				? PlentymarketsImportItemAssociateController::ACTION_DETACHED
				: PlentymarketsImportItemAssociateController::ACTION_CHAINED
		);
		$Config->setDefaultCustomerGroupKey($this->Request()->DefaultCustomerGroupKey);
		$Config->setItemWarehousePercentage($this->Request()->ItemWarehousePercentage);
		$Config->setItemProducerID($this->Request()->ItemProducerID);
		$Config->setOrderMarking1($this->Request()->OrderMarking1);
		$Config->setOrderReferrerID($this->Request()->OrderReferrerID);
		$Config->setOrderPaidStatusID($this->Request()->OrderPaidStatusID);
		$Config->setOrderShopgateMOPIDs(implode('|', $this->Request()->OrderShopgateMOPIDs));
		$Config->setOrderItemTextSyncActionID($this->Request()->OrderItemTextSyncActionID == true ? EXPORT_ORDER_ITEM_TEXT_SYNC : EXPORT_ORDER_ITEM_TEXT_SYNC_NO);
		$Config->setOutgoingItemsOrderStatus($this->Request()->OutgoingItemsOrderStatus);
		$Config->setOutgoingItemsID($this->Request()->OutgoingItemsID);
		$Config->setOutgoingItemsShopwareOrderStatusID($this->Request()->OutgoingItemsShopwareOrderStatusID);
		$Config->setIncomingPaymentShopwarePaymentFullStatusID($this->Request()->IncomingPaymentShopwarePaymentFullStatusID);
		$Config->setIncomingPaymentShopwarePaymentPartialStatusID($this->Request()->IncomingPaymentShopwarePaymentPartialStatusID);
		$Config->setInitialExportChunkSize(max($this->Request()->InitialExportChunkSize, 1));
		$Config->setImportItemChunkSize(max($this->Request()->ImportItemChunkSize, 1));
		$Config->setInitialExportChunksPerRun(max($this->Request()->InitialExportChunksPerRun, -1));
		$Config->setMayLogUsageData($this->Request()->MayLogUsageData == true ? 1 : 0);

		// Customer default values
		$Config->setCustomerDefaultCity($this->Request()->CustomerDefaultCity);
		$Config->setCustomerDefaultHouseNumber($this->Request()->CustomerDefaultHouseNumber);
		$Config->setCustomerDefaultStreet($this->Request()->CustomerDefaultStreet);
		$Config->setCustomerDefaultZipcode($this->Request()->CustomerDefaultZipcode);

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
		PlentymarketsStatus::getInstance()->maySynchronize(false);

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

		$config = $Config->getConfig();

		if (isset($config['OrderShopgateMOPIDs']))
		{
			$orderShopgateMOPIDs = explode('|', $config['OrderShopgateMOPIDs']);
			$config['OrderShopgateMOPIDs'] = array_map('intval', $orderShopgateMOPIDs);
		}
		else
		{
			$config['OrderShopgateMOPIDs'] = array();
		}

		$this->View()->assign(array(
			'success' => true,
			'data' => $config
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

		if (PlentymarketsExportWizard::getInstance()->isActive())
		{
			return $this->View()->assign(array(
				'success' => true,
				'message' => 'Aktion kann nicht ausgeführt werden, da der automatische Export aktiv ist'
			));
		}

		try
		{
			if ($params['doAction'] == 'reset')
			{
				PlentymarketsExportController::getInstance()->reset($params['name']);
				PlentymarketsLogger::getInstance()->message('Export:Initial:' . ucfirst($params['name']), 'Resetted');

				$message = 'Export-Status zurückgesetzt';
			}
			else if ($params['doAction'] == 'erase')
			{
				PlentymarketsExportController::getInstance()->erase($params['name']);
				PlentymarketsLogger::getInstance()->message('Export:Initial:' . ucfirst($params['name']), 'Completely erased');

				$message = 'Export komplett zurückgesetzt';
			}
			else if ($params['doAction'] == 'start')
			{
				PlentymarketsExportController::getInstance()->announce($params['name']);
				PlentymarketsLogger::getInstance()->message('Export:Initial:' . ucfirst($params['name']), 'Announced');

				$message = 'Export vorgemerkt';
			}

			$success = true;
		}
		catch (PlentymarketsExportException $E)
		{
			$success = false;
			$message = $E->getMessage();

			$method = sprintf('set%sExportStatus', $params['name']);
			PlentymarketsConfig::getInstance()->$method('error');

			$method = sprintf('set%sExportLastErrorMessage', $params['name']);
			PlentymarketsConfig::getInstance()->$method($message);

			PlentymarketsLogger::getInstance()->error('Export:Initial:' . ucfirst($params['name']), $message, $E->getCode());
		}

		$settings = $this->getExportStatusList();

		$this->View()->assign(array(
			'success' => $success,
			'message' => $message,
			'data' => $settings[$params['name']]
		));
	}

	/**
	 * Resets a last update timestamp of an entity
	 */
	public function resetImportTimestampAction()
	{
		$entity = $this->Request()->get('entity');

		if (in_array($entity, array('ItemStack', 'ItemPrice', 'ItemStock', 'ItemBundle')))
		{
			PlentymarketsConfig::getInstance()->erase('Import' . $entity . 'LastUpdateTimestamp');
			PlentymarketsLogger::getInstance()->message('Sync:Reset', $entity . ' resetted');
		}

		// Cleanup the mapping – whatsoever
		PlentymarketsGarbageCollector::getInstance()->run(
			PlentymarketsGarbageCollector::ACTION_MAPPING
		);

		$this->View()->assign(array(
			'success' => true,
			'data' => $entity
		));
	}

	/**
	 * Returns the status for each initial export entity
	 *
	 * @return array
	 */
	protected function getExportStatusList()
	{
		return PlentymarketsExportStatusController::getInstance()->getOverview();
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

				if ($forceReload)
				{
					PlentymarketsConfig::getInstance()->setMiscSalesOrderReferrerLastImport(0);
				}

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

			case 'Shop':

				if ($forceReload)
				{
					PlentymarketsConfig::getInstance()->setMiscMultishopsLastImport(0);
				}

				$data = PlentymarketsImportController::getStoreList();
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

			case 'Shop':
				$rows = $DataController->getShops();
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

		$data = PlentymarketsLogger::getInstance()->get(
			$this->Request()->get('start', 0),
			$this->Request()->get('limit', 50),
			$this->Request()->get('type', 0),
			$this->Request()->get('filt0r', '')
		);

		$data['success'] = true;

		$this->View()->assign($data);
	}

	/**
	 * Loads the log identifiers
	 */
	public function getLogIdentifierListAction()
	{
		$this->View()->assign(array(
			'success' => true,
			'data' => PlentymarketsLogger::getInstance()->getIdentifierList()
		));
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

	public function syncItemAction()
	{
		$itemId = (integer) $this->Request()->get('itemId', 0);

		if ($itemId)
		{
			// Controller
			$controller = new PlentymarketsImportControllerItem();

			// StoreIds
			$stores = Shopware()->Db()->fetchAll('
				SELECT plentyID FROM plenty_mapping_shop
			');

			try
			{
				foreach ($stores as $store)
				{
					$controller->importItem($itemId, $store['plentyID']);
				}
			}
			catch (Exception $e)
			{
				PyLog()->error('Fix:Item:Price', $e->getMessage());
			}
		}
	}

	/**
	 *
	 */
	public function fixEmptyItemDetailNumberAction()
	{
		$articleRepository = Shopware()->Models()->getRepository('Shopware\Models\Article\Detail');

		/** @var Shopware\Models\Article\Detail $detail */
		$detail = $articleRepository->findOneBy(array('number' => ''));

		if ($detail)
		{
			$number = PlentymarketsImportItemHelper::getItemNumber();
			$detail->setNumber($number);
			Shopware()->Models()->persist($detail);
			Shopware()->Models()->flush();

			PyLog()->message('Fix:Item:Detail:Number', 'The number of the item detail with the id »' . $detail->getId() . '« has been set to »' . $number . '«.');
		}
		else
		{
			PyLog()->message('Fix:Item:Detail:Number', 'No item without a number has been found');
		}
	}
}
