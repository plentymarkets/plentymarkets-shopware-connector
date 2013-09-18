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
		'Item'
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
		$this->announce($entity);
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
				case 'ItemProperty':
				case 'ItemProducer':
				case 'ItemCategory':
				case 'ItemAttribute':
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
			// PlentymarketsLogger::getInstance()->error('Export:Initial:' . ucfirst($entity), 'Exception ' . get_class($E) . ' on line' . $E->getLine() . ' in file: ' . $E->getFile());
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
				$this->exportOrderById($Order->shopwareId);
			}
			catch (Exception $E)
			{
				PlentymarketsLogger::getInstance()->message('Export:Customer', $E->getMessage());
				$this->Config->setOrderExportLastErrorMessage($E->getMessage());
			}
		}
	}

	/**
	 * Exports customer and delivery address items data.
	 */
	protected function exportCustomers()
	{
		require_once PY_COMPONENTS . 'Export/Entity/PlentymarketsExportEntityCustomer.php';

		// Set running
		$this->Config->setCustomerExportStatus('running');

		// Start
		$this->Config->setCustomerExportTimestampStart(time());
		
		// Repository
		$Repository = Shopware()->Models()->getRepository('Shopware\Models\Customer\Customer');
		
		// Chunk configuration
		$chunk = 0;
		$size = PlentymarketsConfig::getInstance()->getInitialExportChunkSize(self::DEFAULT_CHUNK_SIZE);

		do {
			
			PlentymarketsLogger::getInstance()->message('Export:Initial:Customer', 'Chunk: '. ($chunk + 1));
			$Customers = $Repository->findBy(array(), null, $size, $chunk * $size);

			foreach ($Customers as $Customer)
			{
				$Customer instanceof Shopware\Models\Customer\Customer;
	
				$PlentymarketsExportEntityItem = new PlentymarketsExportEntityCustomer($Customer);
				$PlentymarketsExportEntityItem->export();
			}
			
			++$chunk;
			
		} while (!empty($Customers) && count($Customers) == $size);

		// Set running
		$this->Config->setCustomerExportTimestampFinished(time());
		$this->Config->setCustomerExportStatus('success');
	}

	/**
	 * Exports images, variants, properties item data and items base to make sure, that the corresponding items data exist.
	 */
	protected function exportItems()
	{
		require_once PY_COMPONENTS . 'Export/Entity/PlentymarketsExportEntityItem.php';
		require_once PY_COMPONENTS . 'Export/Entity/PlentymarketsExportEntityItemLinked.php';

		// Set running
		$this->Config->setItemExportStatus('running');

		// Start
		$this->Config->setItemExportTimestampStart(time());
		
		// Repository
		$Repository = Shopware()->Models()->getRepository('Shopware\Models\Article\Article');
		
		// Chunk configuration
		$chunk = 0;
		$size = PlentymarketsConfig::getInstance()->getInitialExportChunkSize(self::DEFAULT_CHUNK_SIZE);
		
		// Cache for crosselling
		$itemIdsToLink = array();
		
			
		$QueryBuilder = Shopware()->Models()->createQueryBuilder();
		$QueryBuilder
			->select('item.id')
			->from('Shopware\Models\Article\Article', 'item');

		do {
			
			// Log the chunk
			PlentymarketsLogger::getInstance()->message('Export:Initial:Item', 'Chunk: '. ($chunk + 1));
			
			// Set Limit and Offset
			$QueryBuilder
				->setFirstResult($chunk * $size)
				->setMaxResults($size);
			
			// Get the items
			$items = $QueryBuilder->getQuery()->getArrayResult();

			foreach ($items as $item)
			{

				try
				{
					// If there is a plenty id for this shopware id,
					// the item has already been exported to plentymarkets
					PlentymarketsMappingController::getItemByShopwareID($item['id']);
					
					// already done
					continue;
				}
				catch (PlentymarketsMappingExceptionNotExistant $E)
				{
				}
				
				$PlentymarketsExportEntityItem = new PlentymarketsExportEntityItem(
					Shopware()->Models()->find('Shopware\Models\Article\Article', $item['id'])
				);
				
				$PlentymarketsExportEntityItem->export();
	
				// Remember the it for the linker
				$itemIdsToLink[] = $item['id'];
			}
			
			++$chunk;
			
		} while (!empty($items) && count($items) == $size);

		// Crosselling
		foreach ($itemIdsToLink as $itemId)
		{
			$PlentymarketsExportEntityItem = new PlentymarketsExportEntityItemLinked(
				Shopware()->Models()->find('Shopware\Models\Article\Article', $itemId)
			);
			$PlentymarketsExportEntityItem->link();
		}

		// Set running
		$this->Config->setItemExportTimestampFinished(time());
		$this->Config->setItemExportStatus('success');
		
		// Reset values
		$this->Config->setImportItemLastUpdateTimestamp(0);
		$this->Config->setImportItemPriceLastUpdateTimestamp(0);
		$this->Config->setImportItemStockLastUpdateTimestamp(0);
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
	 * Exports order by id.
	 *
	 * @param integer $orderID
	 */
	protected function exportOrderById($orderID)
	{
		require_once PY_COMPONENTS . 'Export/Entity/PlentymarketsExportEntityOrder.php';

		$PlentymarketsExportEntityOrder = new PlentymarketsExportEntityOrder($orderID);
		$PlentymarketsExportEntityOrder->export();
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
