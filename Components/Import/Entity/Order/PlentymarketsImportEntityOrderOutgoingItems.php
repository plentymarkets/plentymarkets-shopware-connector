<?php
/**
 * plentymarkets shopware connector
 * Copyright © 2013 plentymarkets GmbH
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 * The texts of the GNU Affero General Public License, supplemented by an additional
 * permission, and of our proprietary license can be found
 * in the LICENSE file you have received along with this program.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 * "plentymarkets" is a registered trademark of plentymarkets GmbH.
 * "shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, titles and interests in the
 * above trademarks remain entirely with the trademark owners.
 *
 * @copyright Copyright (c) 2013, plentymarkets GmbH (http://www.plentymarkets.com)
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */

require_once __DIR__ . '/PlentymarketsImportEntityOrderAbstract.php';

/**
 * PlentymarketsImportEntityOrderOutgoingItems provides the actual order outgoing items import funcionality. 
 * Like the other import entities this class is called in PlentymarketsImportController. It inherits some methods
 * from the entity class PlentymarketsImportEntityOrderAbstract. 
 * The data import takes place based on plentymarkets SOAP-calls.
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsImportEntityOrderOutgoingItems extends PlentymarketsImportEntityOrderAbstract
{
	/**
	 *
	 * @var integer
	 */
	protected static $orderStatus;

	/**
	 *
	 * @var string
	 */
	protected static $action = 'OutgoingItems';

	/**
	 * Prepares the search orders SOAP object
	 * 
	 * @see PlentymarketsImportEntityOrderAbstract::prepare()
	 */
	public function prepare()
	{
		if (is_null(self::$orderStatus))
		{
			self::$orderStatus = PlentymarketsConfig::getInstance()->getOutgoingItemsShopwareOrderStatusID(7);
		}
		
		$timestamp = PlentymarketsConfig::getInstance()->getImportOrderOutgoingItemsLastUpdateTimestamp(0);
		
		$this->log('LastUpdate: ' . date('r', $timestamp));

		if (PlentymarketsConfig::getInstance()->getOutgoingItemsOrderStatus(0))
		{
			$this->Request_SearchOrders->LastUpdateFrom = $timestamp;
			$this->Request_SearchOrders->OrderStatus = (float) PlentymarketsConfig::getInstance()->getOutgoingItemsOrderStatus();
			$this->log('Mode: Status (' . $this->Request_SearchOrders->OrderStatus . ')');
		}

		else
		{
			$this->Request_SearchOrders->OrderCompletedFrom = $timestamp;
			$this->log('Mode: Outgoing items booked');
		}
	}

	/**
	 * Handles the actual import
	 *
	 * @param integer $shopwareOrderId
	 * @param PlentySoapObject_OrderHead $Order
	 */
	public function handle($shopwareOrderId, $Order)
	{
		self::$OrderModule->setOrderStatus($shopwareOrderId, self::$orderStatus, false, 'plentymarkets');
	}
}
