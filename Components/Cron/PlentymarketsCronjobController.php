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

require_once PY_SOAP . 'Client/PlentymarketsSoapClient.php';
require_once PY_COMPONENTS . 'Config/PlentymarketsConfig.php';
require_once PY_COMPONENTS . 'Mapping/PlentymarketsMappingController.php';
require_once PY_COMPONENTS . 'Utils/PlentymarketsUtils.php';
require_once PY_COMPONENTS . 'Utils/PlentymarketsGarbageCollector.php';
require_once PY_COMPONENTS . 'Import/PlentymarketsImportController.php';
require_once PY_COMPONENTS . 'Import/Stack/PlentymarketsImportStackItem.php';
require_once PY_COMPONENTS . 'Export/PlentymarketsExportController.php';
require_once PY_COMPONENTS . 'Export/Continuous/PlentymarketsExportContinuousController.php';

/**
 * The class CronjobController provides all methods for data import and export. CronjobController is used in
 * Shopware_Plugins_Backend_PlentyConnector_Bootstrap to register and run different cronjobs.
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsCronjobController
{

	/**
	 * INTERVAL_IMPORT_ITEM is a constant parameter, which is needed to register a new cronjob.
	 *
	 * @var integer
	 */
	CONST INTERVAL_IMPORT_ITEM = 3600;

	/**
	 * INTERVAL_IMPORT_ITEM_PRICE is a constant parameter, which is needed to register a new cronjob.
	 *
	 * @var integer
	 */
	CONST INTERVAL_IMPORT_ITEM_PRICE = 3600;

	/**
	 * INTERVAL_IMPORT_ITEM_STOCK is a constant parameter, which is needed to register a new cronjob.
	 *
	 * @var integer
	 */
	CONST INTERVAL_IMPORT_ITEM_STOCK = 900;

	/**
	 * INTERVAL_IMPORT_ORDER is a constant parameter, which is needed to register a new cronjob.
	 *
	 * @var integer
	 */
	CONST INTERVAL_IMPORT_ORDER = 3600;

	/**
	 * INTERVAL_EXPORT is a constant parameter, which is needed to register a new cronjob.
	 *
	 * @var integer
	 */
	CONST INTERVAL_EXPORT = 300;

	/**
	 * INTERVAL_EXPORT_ORDER is a constant parameter, which is needed to register a new cronjob.
	 *
	 * @var integer
	 */
	CONST INTERVAL_EXPORT_ORDER = 900;

	/**
	 * INTERVAL_EXPORT_ORDER_INCOMING_PAYMENT is a constant parameter, which is needed to register a new cronjob.
	 *
	 * @var integer
	 */
	CONST INTERVAL_EXPORT_ORDER_INCOMING_PAYMENT = 1800;

	/**
	 * PlentymarketsCronjobController object data.
	 *
	 * @var PlentymarketsCronjobController
	 */
	protected static $Instance;

	/**
	 * Indicates whether a cronjob may run or not.
	 *
	 * @var boolean
	 */
	protected $mayRun = true;

	/**
	 * PlentymarketsConfig object data.
	 *
	 * @var PlentymarketsConfig
	 */
	protected $Config;

	/**
	 * Checks whether any cronjob may run or not.
	 */
	protected function __construct()
	{
		// Check whether any cronjob my be executed due to api status
		$this->mayRun = PlentymarketsUtils::checkDxStatus()
						&& PlentymarketsConfig::getInstance()->getMayDatexActual(false);

		$this->Config = PlentymarketsConfig::getInstance();
	}

	/**
	 * If an instance of PlentymarketsCronjobController exists, it returns this instance.
	 * Else it creates a new instance of PlentymarketsCronjobController.
	 *
	 * @return PlentymarketsCronjobController
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
	 * Runs the cleanup cronjob.
	 *
	 * @param Shopware_Components_Cron_CronJob $Job
	 */
	public function runCleanup(Shopware_Components_Cron_CronJob $Job)
	{
		if (!$this->mayRun)
		{
			return;
		}

		$PlentymarketsGarbageCollector = PlentymarketsGarbageCollector::getInstance();
		$PlentymarketsGarbageCollector->run(PlentymarketsGarbageCollector::ACTION_MAPPING);
	}

	/**
	 * Runs the item cleanup cronjob.
	 *
	 * @param Shopware_Components_Cron_CronJob $Job
	 */
	public function runLogCleanup(Shopware_Components_Cron_CronJob $Job)
	{
		if (!$this->mayRun)
		{
			return;
		}

		PlentymarketsLogger::getInstance()->message('Cleanup:Log', 'Starting');

		$PlentymarketsGarbageCollector = PlentymarketsGarbageCollector::getInstance();
		$PlentymarketsGarbageCollector->run(PlentymarketsGarbageCollector::ACTION_LOG);

		PlentymarketsLogger::getInstance()->message('Cleanup:Log', 'Finished');
	}

	/**
	 * Runs the item cleanup cronjob.
	 *
	 * @param Shopware_Components_Cron_CronJob $Job
	 */
	public function runItemCleanup(Shopware_Components_Cron_CronJob $Job)
	{
		if (!$this->mayRun)
		{
			return;
		}

		PlentymarketsLogger::getInstance()->message('Cleanup:Item', 'Starting');

		$PlentymarketsGarbageCollector = PlentymarketsGarbageCollector::getInstance();
		$PlentymarketsGarbageCollector->run(PlentymarketsGarbageCollector::ACTION_PRUNE_ITEMS);

		PlentymarketsLogger::getInstance()->message('Cleanup:Item', 'Finished');
	}

	/**
	 * Runs the mapping cleanup cronjob.
	 *
	 * @param Shopware_Components_Cron_CronJob $Job
	 */
	public function runMappingCleanup(Shopware_Components_Cron_CronJob $Job)
	{
		// Check the connection
		if (!PlentymarketsUtils::checkApiConnectionStatus())
		{
			return;
		}

		PlentymarketsLogger::getInstance()->message('Cleanup:Mapping', 'Starting');

		// Reset the timestamps
		PlentymarketsConfig::getInstance()->setMiscCustomerClassLastImport(0);
		PlentymarketsConfig::getInstance()->setMiscMethodsOfPaymentLastImport(0);
		PlentymarketsConfig::getInstance()->setMiscSalesOrderReferrerLastImport(0);
		PlentymarketsConfig::getInstance()->setMiscShippingProfilesLastImport(0);
		PlentymarketsConfig::getInstance()->setMiscMultishopsLastImport(0);
		PlentymarketsConfig::getInstance()->setMiscVatLastImport(0);

		// Get fresh data
		PlentymarketsImportController::getCustomerClassList();
		PlentymarketsImportController::getMethodOfPaymentList();
		PlentymarketsImportController::getOrderReferrerList();
		PlentymarketsImportController::getShippingProfileList();
		PlentymarketsImportController::getStoreList();
		PlentymarketsImportController::getVatList();

		PlentymarketsLogger::getInstance()->message('Cleanup:Mapping', 'Finished');
	}

	/**
	 * Runs the order export cronjob.
	 *
	 * @param Shopware_Components_Cron_CronJob $Job
	 */
	public function runOrderExport(Shopware_Components_Cron_CronJob $Job)
	{
		$this->Config->setExportOrderLastRunTimestamp(time());
		$this->Config->setExportOrderNextRunTimestamp(time() + $Job->getJob()->getInterval());

		if (!$this->mayRun)
		{
			$this->Config->setExportOrderStatus(0);
			return;
		}

		try
		{
			PlentymarketsExportContinuousController::getInstance()->run('Order');
			$this->Config->setExportOrderStatus(1);
			$this->Config->eraseExportOrderError();
		}
		catch (Exception $E)
		{
			$this->Config->setExportOrderStatus(2);
			$this->Config->setExportOrderError($E->getMessage());
		}
	}

	/**
	 * Runs the order incoming item export cronjob.
	 *
	 * @param Shopware_Components_Cron_CronJob $Job
	 */
	public function runOrderIncomingPaymentExport(Shopware_Components_Cron_CronJob $Job)
	{
		$this->Config->setExportOrderIncomingPaymentLastRunTimestamp(time());
		$this->Config->setExportOrderIncomingPaymentNextRunTimestamp(time() + $Job->getJob()->getInterval());

		if (!$this->mayRun)
		{
			$this->Config->setExportOrderIncomingPaymentStatus(0);
			return;
		}

		try
		{
			PlentymarketsExportContinuousController::getInstance()->run('OrderIncomingPayment');
			$this->Config->setExportOrderIncomingPaymentStatus(1);
			$this->Config->eraseExportOrderIncomingPaymentError();
		}
		catch (Exception $E)
		{
			$this->Config->setExportOrderIncomingPaymentStatus(2);
			$this->Config->setExportOrderIncomingPaymentError($E->getMessage());
		}
	}

	/**
	 * Runs the order import cronjob.
	 *
	 * @param Shopware_Components_Cron_CronJob $Job
	 */
	public function runOrderImport(Shopware_Components_Cron_CronJob $Job)
	{
		$this->Config->setImportOrderLastRunTimestamp(time());
		$this->Config->setImportOrderNextRunTimestamp(time() + $Job->getJob()->getInterval());

		if (!$this->mayRun)
		{
			$this->Config->setImportOrderStatus(0);
			return;
		}

		try
		{
			PlentymarketsImportController::importOrders();
			$this->Config->setImportOrderStatus(1);
			$this->Config->eraseImportOrderError();
		}
		catch (Exception $E)
		{
			$this->Config->setImportOrderStatus(2);
			$this->Config->setImportOrderError($E->getMessage());
		}
	}

	/**
	 * Runs the export cronjob.
	 *
	 * @param Shopware_Components_Cron_CronJob $Job
	 */
	public function runExport(Shopware_Components_Cron_CronJob $Job)
	{
		try
		{
			PlentymarketsExportController::getInstance()->export();
		}
		catch (PlentymarketsExportException $E)
		{
			PlentymarketsLogger::getInstance()->error('Cron:Export', $E->getMessage(), $E->getCode());
		}
		catch (Exception $E)
		{
			PlentymarketsLogger::getInstance()->error('Cron:Export', $E->getMessage(), 1000);
			PlentymarketsLogger::getInstance()->error('Cron:Export', get_class($E), 1000);
			PlentymarketsLogger::getInstance()->error('Cron:Export', $E->getTraceAsString(), 1000);
		}

		require_once PY_COMPONENTS . 'Export/PlentymarketsExportWizard.php';
		$PlentymarketsExportWizard = PlentymarketsExportWizard::getInstance();
		$PlentymarketsExportWizard->conjure();
	}

	/**
	 * Runs the item import cronjob.
	 *
	 * @param Shopware_Components_Cron_CronJob $Job
	 */
	public function runItemImport(Shopware_Components_Cron_CronJob $Job)
	{
		$this->Config->setImportItemLastRunTimestamp(time());
		$this->Config->setImportItemNextRunTimestamp(time() + $Job->getJob()->getInterval());

		if (!$this->mayRun)
		{
			$this->Config->setImportItemStatus(0);
			return;
		}

		try
		{
			PlentymarketsImportController::importItems();
			$this->Config->setImportItemStatus(1);
			$this->Config->eraseImportItemError();
		}
		catch (Exception $E)
		{
			$this->Config->setImportItemStatus(2);
			$this->Config->setImportItemError($E->getMessage());
		}
	}

	/**
	 * Runs the item import stack cronjob.
	 *
	 * @param Shopware_Components_Cron_CronJob $Job
	 */
	public function runItemImportStackUpdate(Shopware_Components_Cron_CronJob $Job)
	{
		$this->Config->setImportItemStackLastRunTimestamp(time());
		$this->Config->setImportItemStackNextRunTimestamp(time() + $Job->getJob()->getInterval());

		if (!$this->mayRun)
		{
			return;
		}

		try
		{
			PlentymarketsImportStackItem::getInstance()->update();
			$this->Config->setImportItemStackStatus(1);
			$this->Config->eraseImportItemStackError();
		}
		catch (Exception $E)
		{
			$this->Config->setImportItemStackStatus(2);
			$this->Config->setImportItemStackError($E->getMessage());
		}
	}

	/**
	 * Runs the item price import cronjob.
	 *
	 * @param Shopware_Components_Cron_CronJob $Job
	 */
	public function runItemPriceImport(Shopware_Components_Cron_CronJob $Job)
	{
		$this->Config->setImportItemPriceLastRunTimestamp(time());
		$this->Config->setImportItemPriceNextRunTimestamp(time() + $Job->getJob()->getInterval());

		if (!$this->mayRun)
		{
			$this->Config->setImportItemPriceStatus(0);
			return;
		}

		try
		{
			PlentymarketsImportController::importItemPrices();
			$this->Config->setImportItemPriceStatus(1);
			$this->Config->eraseImportItemPriceError();
		}
		catch (Exception $E)
		{
			$this->Config->setImportItemPriceStatus(2);
			$this->Config->setImportItemPriceError($E->getMessage());
		}
	}

	/**
	 * Runs the item stock import cronjob.
	 *
	 * @param Shopware_Components_Cron_CronJob $Job
	 */
	public function runItemStockImport(Shopware_Components_Cron_CronJob $Job)
	{
		$this->Config->setImportItemStockLastRunTimestamp(time());
		$this->Config->setImportItemStockNextRunTimestamp(time() + $Job->getJob()->getInterval());

		if (!$this->mayRun)
		{
			$this->Config->setImportItemStockStatus(0);
			return;
		}

		try
		{
			PlentymarketsImportController::importItemStocks();
			$this->Config->setImportItemStockStatus(1);
			$this->Config->eraseImportItemStockError();
		}
		catch (Exception $E)
		{
			$this->Config->setImportItemStockStatus(2);
			$this->Config->setImportItemStockError($E->getMessage());
		}
	}
}
