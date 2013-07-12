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
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsExportController
{

	/**
	 *
	 * @var PlentymarketsExportController
	 */
	protected static $Instance;

	/**
	 *
	 * @var PlentymarketsConfig
	 */
	protected $Config;

	/**
	 *
	 * @var boolean
	 */
	protected $isRunning = false;

	/**
	 *
	 * @var boolean
	 */
	protected $isSuccessFul = true;

	/**
	 */
	protected function __construct()
	{
		//
		$this->Config = PlentymarketsConfig::getInstance();

		// Check whether a process is running
		$this->isRunning = (boolean) $this->Config->getIsExportRunning(false);

		// Check whether every export is finished successfully
		$this->isSuccessFul = $this->isSuccessFul && ($this->Config->getItemCategoryExportStatus('open') == 'success');
		$this->isSuccessFul = $this->isSuccessFul && ($this->Config->getItemAttributeExportStatus('open') == 'success');
		$this->isSuccessFul = $this->isSuccessFul && ($this->Config->getItemPropertyExportStatus('open') == 'success');
		$this->isSuccessFul = $this->isSuccessFul && ($this->Config->getItemProducerExportStatus('open') == 'success');
		$this->isSuccessFul = $this->isSuccessFul && ($this->Config->getItemExportStatus('open') == 'success');
	}

	/**
	 *
	 * @return boolean
	 */
	public function isSuccessFul()
	{
		return $this->isSuccessFul;
	}

	/**
	 *
	 * @param string $entity
	 */
	public function restart($entity)
	{
		$this->announce($entity);
	}

	/**
	 *
	 * @param string $entity
	 * @throws \Exception
	 */
	public function announce($entity)
	{
		if ($this->isRunning == true)
		{
			throw new \Exception('Another export is running at this very moment');
		}

		// Check whether another export is waiting to be executed
		$waiting = $this->Config->getExportEntityPending(false);
		if ($waiting == $entity)
		{
			throw new \Exception('The export of entity ' . $entity . ' has already announced');
		}

		if ($waiting != false)
		{
			throw new \Exception('Another export is waiting to be carried out');
		}

		//
		$this->Config->setExportEntityPending($entity);

		$method = sprintf('set%sExportStatus', $entity);
		$this->Config->$method('pending');
	}

	/**
	 * Start the pending export.
	 *
	 * Called from a cronjob!
	 *
	 * @throws \Exception
	 */
	public function export()
	{
		if ($this->isRunning == true)
		{
			throw new \Exception('Another export is running at this very moment');
		}

		$entity = $this->Config->getExportEntityPending(false);

		if ($entity == false)
		{
			// No exception.. or the log will ne spammed
			return;
		}

		// Set the running flag
		$this->Config->setExportEntityPending(false);
		$this->Config->setIsExportRunning(1);

		//
		PlentymarketsLogger::getInstance()->message('Export:Initial:' . ucfirst($entity), 'Starting');

		try
		{
			switch ($entity)
			{
				// Entities
				case 'ItemProperty':
				case 'ItemProducer':
				case 'ItemCategory':
				case 'ItemAttribute':
					$this->_export($entity);
					break;

				// Items
				case 'Item':
					$this->exportItems();
					break;

				// Customers
				case 'Customer':
					$this->exportCustomers();
					break;
			}

			PlentymarketsLogger::getInstance()->message('Export:Initial:' . ucfirst($entity), 'Done!');
		}
		catch (Exception $E)
		{
			PlentymarketsLogger::getInstance()->error('Export:Initial:' . ucfirst($entity), 'Exception ' . get_class($E) . ' on line' . $E->getLine() . ' in file: ' . $E->getFile());
			PlentymarketsLogger::getInstance()->error('Export:Initial:' . ucfirst($entity), $E->getMessage());

			$method = sprintf('set%sExportLastErrorMessage', $entity);
			$this->Config->$method($E->getMessage());

			$method = sprintf('set%sExportStatus', $entity);
			$this->Config->$method('error');
		}

		$this->Config->setIsExportRunning(0);
	}

	/**
	 *
	 * @return PlentymarketsExportController
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
	 */
	public function exportOrders()
	{
		if ($this->isRunning == true)
		{
			throw new \Exception('Another export is running at this very moment');
		}

		$this->Config->setIsExportRunning(1);

		// Get all the orders, that are not yet exported to plentymarkets
		$Result = Shopware()->Db()->query('
			SELECT
					shopwareId, numberOfTries, timestampLastTry
				FROM plenty_order
				WHERE plentyOrderId IS NULL
		');

		while (($Order = $Result->fetchObject()) && is_object($Order))
		{
			if ($Order->numberOfTries > 1000)
			{
				continue;
			}

			if (!is_null($Order->timestampLastTry) && $Order->timestampLastTry < time() - (60 * 15))
			{
				continue;
			}

			try
			{
				$this->_exportOrderById($Order->shopwareId);
			}
			catch (Exception $e)
			{
				$this->Config->setOrderExportLastErrorMessage($e->getMessage());
			}
		}

		$this->Config->setIsExportRunning(0);
	}

	/**
	 * Export the items
	 */
	protected function exportCustomers()
	{
		require_once PY_COMPONENTS . 'Export/Entity/PlentymarketsExportEntityCustomer.php';

		// Set running
		$this->Config->setCustomerExportStatus('running');

		// Start
		$this->Config->setCustomerExportTimestampStart(time());

		$Customers = Shopware()->Models()
			->getRepository('Shopware\Models\Customer\Customer')
			->findAll();

		foreach ($Customers as $Customer)
		{
			$Customer instanceof Shopware\Models\Customer\Customer;

			$PlentymarketsExportEntityItem = new PlentymarketsExportEntityCustomer($Customer);
			$PlentymarketsExportEntityItem->export();
		}

		// Set running
		$this->Config->setCustomerExportTimestampFinished(time());
		$this->Config->setCustomerExportStatus('success');
	}

	/**
	 * Export the items
	 */
	protected function exportItems()
	{
		require_once PY_COMPONENTS . 'Export/Entity/PlentymarketsExportEntityItem.php';
		require_once PY_COMPONENTS . 'Export/Entity/PlentymarketsExportEntityItemLinked.php';

		// Set running
		$this->Config->setItemExportStatus('running');

		// Start
		$this->Config->setItemExportTimestampStart(time());

		$Items = Shopware()->Models()
			->getRepository('Shopware\Models\Article\Article')
			->findAll();

		$ItemsToLink = array();
		foreach ($Items as $Item)
		{
			$PlentymarketsExportEntityItem = new PlentymarketsExportEntityItem($Item);

			if ($PlentymarketsExportEntityItem->export())
			{
				$ItemsToLink[] = $Item;
			}
		}

		// Crosselling
		foreach ($ItemsToLink as $Item)
		{
			$PlentymarketsExportEntityItem = new PlentymarketsExportEntityItemLinked($Item);
			$PlentymarketsExportEntityItem->link();
		}

		// Set running
		$this->Config->setItemExportTimestampFinished(time());
		$this->Config->setItemExportStatus('success');
	}

	/**
	 *
	 */
	public function exportIncomingPayments()
	{
		require_once PY_COMPONENTS . 'Export/Entity/PlentymarketsExportEntityIncomingPayment.php';

		// Set running
		$this->Config->setItemIncomingPaymentExportStatus('running');

		// Start
		$this->Config->setItemIncomingPaymentExportStart(time());

		$now = time();
		$lastUpdateTimestamp = date('Y-m-d H:i:s', $this->Config->getItemIncomingPaymentExportLastUpdate(time()));
		$status = $this->Config->getOrderPaidStatusID(12);

		$Result = Shopware()->Db()->query('
			SELECT
					DISTINCT orderID
				FROM s_order_history
					JOIN plenty_order ON shopwareId = orderID
				WHERE
					change_date > \'' . $lastUpdateTimestamp . '\' AND
					payment_status_id = ' . $status . ' AND
					IFNULL(plentyOrderPaidStatus, 0) != 1
		');

		while (($order = $Result->fetchObject()) && is_object($order))
		{
			try
			{
				$ExportEntityIncomingPayment = new PlentymarketsExportEntityIncomingPayment($order->orderID);
				$ExportEntityIncomingPayment->book();
			}
			catch (Exception $e)
			{
			}
		}

		// Set running
		$this->Config->setItemIncomingPaymentExportTimestampFinished(time);
		$this->Config->setItemIncomingPaymentExportLastUpdate($now);
		$this->Config->setItemIncomingPaymentExportStatus('success');
	}

	/**
	 *
	 * @param string $entity
	 * @return boolean
	 */
	protected function isSuccessfullyFinished($entity)
	{
		$method = sprintf('get%sExportStatus', $entity);
		return $this->Config->$method('…') == 'success';
	}

	/**
	 *
	 * @param integer $orderID
	 */
	protected function _exportOrderById($orderID)
	{
		require_once PY_COMPONENTS . 'Export/Entity/PlentymarketsExportEntityOrder.php';

		$PlentymarketsExportEntityOrder = new PlentymarketsExportEntityOrder($orderID);
		$PlentymarketsExportEntityOrder->export();
	}

	/**
	 *
	 * @param string $entity
	 */
	protected function _export($entity)
	{
		if ($this->isSuccessfullyFinished($entity))
		{
			return;
		}

		$class = sprintf('PlentymarketsExportEntity%s', $entity);

		require_once PY_COMPONENTS . 'Export/Entity/' . $class . '.php';

		// Set running
		$methodStatus = sprintf('set%sExportStatus', $entity);
		$this->Config->$methodStatus('running');

		// Start
		$methodStart = sprintf('set%sExportTimestampStart', $entity);
		$this->Config->$methodStart(time());

		// Run the export
		$PlentymarketsExport = new $class();
		$PlentymarketsExport->export();

		// Finished
		$methodStart = sprintf('set%sExportTimestampFinished', $entity);
		$this->Config->$methodStart(time());

		$this->Config->$methodStatus('success');
	}
}
