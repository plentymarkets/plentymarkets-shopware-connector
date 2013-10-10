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
 * The class PlentymarketsExportController does the actual export for different cronjobs e.g. in the class PlentymarketsCronjobController.
 * It uses the different export entities in /Export/Entity, for example PlentymarketsExportEntityCustomer.
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsExportController
{

	/**
	 *
	 * @var integer
	 */
	const DEFAULT_CHUNKS_PER_RUN = -1;

	/**
	 *
	 * @var integer
	 */
	const DEFAULT_CHUNK_SIZE = 250;

	/**
	 * PlentymarketsExportController object data.
	 *
	 * @var PlentymarketsExportController
	 */
	protected static $Instance;

	/**
	 * This array defines the order in which the exports have to be carried out.
	 * @var array
	 */
	protected static $order = array(
		'ItemCategory',
		'ItemAttribute',
		'ItemProperty',
		'ItemProducer',
		'Item',
		'ItemCrossSelling',
	);

	/**
	 *
	 * @var array
	 */
	protected static $mapping = array(
		'ItemCategory' => 'plenty_mapping_category',
		'ItemAttribute' => array(
			'plenty_mapping_attribute_group',
			'plenty_mapping_attribute_option',
		),
		'ItemProperty' => array(
			'plenty_mapping_property',
			'plenty_mapping_property_group',
		),
		'ItemProducer' => 'plenty_mapping_producer',
		'Item' => array(
			'plenty_mapping_item',
			'plenty_mapping_item_variant',
		),
		'Customer' => 'plenty_mapping_customer_billing_address',
		'ItemCrossSelling' => array()
	);

	/**
	 * PlentymarketsConfig object data.
	 *
	 * @var PlentymarketsConfig
	 */
	protected $Config;

	/**
	 * Indicates whether an export process is running.
	 *
	 * @var boolean
	 */
	protected $isRunning = false;

	/**
	 * Indicates whether every export is finished completely.
	 *
	 * @var boolean
	 */
	protected $isComplete = true;

	/**
	 * Indicates whether an export may run or not.
	 *
	 * @var boolean
	 */
	protected $mayRun = false;

	/**
	 * Prepares config data and checks different conditions like finished mapping.
	 */
	protected function __construct()
	{
		//
		$this->Config = PlentymarketsConfig::getInstance();

		// Check whether a process is running
		$this->isRunning = (boolean) $this->Config->getIsExportRunning(false);

		// Check whether settings and mapping are done
		$this->mayRun = PlentymarketsMappingController::isComplete() && $this->Config->isComplete();

		// Check whether every export is finished completely
		$this->isComplete = $this->isComplete && ($this->Config->getItemCategoryExportStatus('open') == 'success');
		$this->isComplete = $this->isComplete && ($this->Config->getItemAttributeExportStatus('open') == 'success');
		$this->isComplete = $this->isComplete && ($this->Config->getItemPropertyExportStatus('open') == 'success');
		$this->isComplete = $this->isComplete && ($this->Config->getItemProducerExportStatus('open') == 'success');
		$this->isComplete = $this->isComplete && ($this->Config->getItemExportStatus('open') == 'success');
		$this->isComplete = $this->isComplete && ($this->Config->getItemCrossSellingExportStatus('open') == 'success');
	}

	/**
	 * The returned value indicates whether the settings and mapping are done.
	 *
	 * @return boolean
	 */
	public function isComplete()
	{
		return $this->isComplete;
	}

	/**
	 * Re-announces an export
	 *
	 * @param string $entity
	 */
	public function restart($entity)
	{
		// Last chunk
		$methodLastChunk = sprintf('set%sExportLastChunk', $entity);
		$this->Config->$methodLastChunk('');

		$this->announce($entity);
	}

	/**
	 * Resets an export status
	 *
	 * @param string $entity
	 */
	public function reset($entity, $resetRunning=true)
	{
		// Reset running status
		if ($resetRunning)
		{
			$this->Config->setExportEntityPending(false);
			$this->Config->setIsExportRunning(0);
		}

		$methodStatus = sprintf('set%sExportStatus', $entity);
		$this->Config->$methodStatus('open');

		// Start
		$methodStart = sprintf('set%sExportTimestampStart', $entity);
		$this->Config->$methodStart('');

		// Finished
		$methodStart = sprintf('set%sExportTimestampFinished', $entity);
		$this->Config->$methodStart('');

		// Error
		$methodError = sprintf('set%sExportLastErrorMessage', $entity);
		$this->Config->$methodError('');

		// Last chunk
		$methodLastChunk = sprintf('set%sExportLastChunk', $entity);
		$this->Config->$methodLastChunk('');
	}

	/**
	 * Erases an export status and the mapping
	 *
	 * @param string $entity
	 */
	public function erase($entity)
	{
		// Status
		$this->reset($entity, false);

		// Delete Mapping
		foreach ((array) self::$mapping[$entity] as $tableName)
		{
			try
			{
				Shopware()->Db()->delete($tableName);
				PlentymarketsLogger::getInstance()->message('Export:Initial:' . $entity, 'Truncated table ' . $tableName);
			}
			catch (Exception $E)
			{
			}
		}
	}

	/**
	 * This method checks different export states like if an other export is running at the moment, or
	 * an export has already announced. In case of complied conditions the actual export gets the state "pending".
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

		// Check whether settings and mapping is complete
		if ($this->mayRun == false)
		{
			throw new \Exception('Either the mapping or the settings are not finished');
		}

		// Check whether or not the order is correct
		$index = array_search($entity, self::$order);
		if ($index > 0)
		{
			$previous = self::$order[$index - 1];
			if (!$this->isSuccessfullyFinished($previous))
			{
				throw new \Exception('The previous entity "'. $previous .'" is not finished successfully');
			}
		}

		//
		$this->Config->setExportEntityPending($entity);

		$method = sprintf('set%sExportStatus', $entity);
		$this->Config->$method('pending');
	}

	/**
	 * Starts the actual pending export.
	 *
	 * @throws \Exception
	 */
	public function export()
	{
		if ($this->isRunning == true)
		{
			throw new \Exception('Another export is running at this very moment');
		}

		// Check whether settings and mapping is complete
		if ($this->mayRun == false)
		{
			throw new \Exception('Either the mapping or the settings are not finished');
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
				case 'ItemCategory':
				case 'ItemAttribute':
				case 'ItemProperty':
				case 'ItemProducer':
				case 'ItemCrossSelling':
					$this->exportEntity($entity);
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
			PlentymarketsLogger::getInstance()->error('Export:Initial:' . ucfirst($entity), $E->getMessage());

			$method = sprintf('set%sExportLastErrorMessage', $entity);
			$this->Config->$method($E->getMessage());

			$method = sprintf('set%sExportStatus', $entity);
			$this->Config->$method('error');
		}

		$this->Config->setIsExportRunning(0);
	}

	/**
	 * If an instance of PlentymarketsExportController exists, it returns this instance.
	 * Else it creates a new instance of PlentymarketsExportController.
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
	 * Exports all orders, which are not yet exported to plentymarkets and customers to make sure,
	 * that the corresponding customers exist.
	 */
	public function exportOrders()
	{
		require_once PY_COMPONENTS . 'Export/Entity/PlentymarketsExportEntityOrder.php';

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
				$PlentymarketsExportEntityOrder = new PlentymarketsExportEntityOrder($Order->shopwareId);
				$PlentymarketsExportEntityOrder->export();
			}
			catch (Exception $E)
			{
				PlentymarketsLogger::getInstance()->message('Export:Order', $E->getMessage());
				$this->Config->setOrderExportLastErrorMessage($E->getMessage());
			}
		}
	}

	/**
	 * Exports customer and delivery address items data.
	 */
	protected function exportCustomers()
	{
		require_once PY_COMPONENTS . 'Export/Controller/PlentymarketsExportControllerCustomer.php';
		PlentymarketsExportControllerCustomer::getInstance()->run();
	}

	/**
	 * Exports images, variants, properties item data and items base to make sure, that the corresponding items data exist.
	 */
	protected function exportItems()
	{
		require_once PY_COMPONENTS . 'Export/Controller/PlentymarketsExportControllerItem.php';
		PlentymarketsExportControllerItem::getInstance()->run();
	}

	/**
	 * Exports incoming payments data.
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
		$this->Config->setItemIncomingPaymentExportTimestampFinished(time());
		$this->Config->setItemIncomingPaymentExportLastUpdate($now);
		$this->Config->setItemIncomingPaymentExportStatus('success');
	}

	/**
	 * Checks whether an export has successfully finished.
	 *
	 * @param string $entity
	 * @return boolean
	 */
	protected function isSuccessfullyFinished($entity)
	{
		$method = sprintf('get%sExportStatus', $entity);
		return $this->Config->$method() == 'success';
	}

	/**
	 * Exports one Entity.
	 *
	 * @param string $entity
	 */
	protected function exportEntity($entity)
	{
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
