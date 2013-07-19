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

/**
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
abstract class PlentymarketsImportEntityOrderAbstract
{

	/**
	 *
	 * @var string
	 */
	protected static $action;

	/**
	 *
	 * @var sOrder
	 */
	protected static $OrderModule;

	/**
	 *
	 * @var PlentySoapRequest_SearchOrders
	 */
	protected $Request_SearchOrders;

	/**
	 *
	 * @var integer
	 */
	protected $timestamp;

	/**
	 *
	 */
	public function __construct()
	{
		$this->Request_SearchOrders = new PlentySoapRequest_SearchOrders();
		$this->Request_SearchOrders->GetIncomingPayments = false; // boolean
		$this->Request_SearchOrders->GetOrderCustomerAddress = false; // boolean
		$this->Request_SearchOrders->GetOrderDeliveryAddress = false; // boolean
		$this->Request_SearchOrders->GetOrderDocumentNumbers = false; // boolean
		$this->Request_SearchOrders->GetOrderInfo = false; // boolean
		$this->Request_SearchOrders->GetParcelService = false; // boolean
		$this->Request_SearchOrders->GetSalesOrderProperties = false; // boolean
		$this->Request_SearchOrders->StoreID = PlentymarketsConfig::getInstance()->getStoreID(); // int
		$this->Request_SearchOrders->OrderType = 'order'; // string
		$this->Request_SearchOrders->Page = 0;

		$this->timestamp = time();

		//
		if (is_null(self::$OrderModule))
		{
			self::$OrderModule = Shopware()->Modules()->Order();
		}
	}

	abstract public function prepare();

	abstract public function finish();

	/**
	 *
	 * @param integer $shopwareOrderId
	 * @param PlentySoapObject_OrderHead $Order
	 */
	abstract public function handle($shopwareOrderId, $Order);

	/**
	 *
	 * @param string $message
	 * @param string $type
	 */
	protected function log($message, $type = 'message')
	{
		PlentymarketsLogger::getInstance()->$type('Sync:Order:' . static::$action, $message);
	}

	/**
	 *
	 */
	public function import()
	{
		$this->prepare();

		// Helper
		$numberOfOrdersUpdated = 0;

		do
		{
			$Response_SearchOrders = PlentymarketsSoapClient::getInstance()->SearchOrders($this->Request_SearchOrders);

			//
			$pages = max($Response_SearchOrders->Pages, 1);

			$this->log('Page: ' . ($Request_SearchOrders->Page + 1) . '/' . $pages);

			if ($Response_SearchOrders->Success == false)
			{
				$this->log('Failed', 'error');
				break;
			}

			$this->log('Received ' . count($Response_SearchOrders->Orders->item) . ' items');

			// Jeden Artikel in Shopware "importieren"
			foreach ($Response_SearchOrders->Orders->item as $Order)
			{
				$Order = $Order->OrderHead;
				$Order instanceof PlentySoapObject_OrderHead;
				try
				{
					$orderId = $Order->ExternalOrderID;
					if (strstr($orderId, 'Swag/') === false)
					{
						$this->log('The sales order with the external order id ' . $Order->ExternalOrderID . ' could not be updated because it isn\'t a shopware order.', 'error');
						continue;
					}

					$SHOPWARE_orderId = PlentymarketsUtils::getShopwareIDFromExternalOrderID($orderId);
					if ($SHOPWARE_orderId <= 0)
					{
						$this->log('The sales order with the external order id ' . $Order->ExternalOrderID . ' could not be updated.', 'error');
						continue;
					}

					$this->handle($SHOPWARE_orderId, $Order);
					$this->log('The sales order with the id ' . $orderId . ' has been updated.');

					++$numberOfOrdersUpdated;
				}
				catch (Exception $E)
				{
					$this->log('The sales order with the external order id ' . $Order->ExternalOrderID . ' could not be updated.', 'error');
					$this->log($E->getMessage(), 'error');
				}
			}
		}

		// Until all pages are received
		while (++$Request_SearchOrders->Page < $Response_SearchOrders->Pages);

		//
		$this->log($numberOfOrdersUpdated . ' sales orders have been updated.');

		//
		$this->finish();
	}
}
