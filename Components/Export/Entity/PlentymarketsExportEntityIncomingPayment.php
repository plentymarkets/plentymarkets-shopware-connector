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

require_once PY_SOAP . 'Models/PlentySoapObject/AddIncomingPayments.php';
require_once PY_SOAP . 'Models/PlentySoapRequest/AddIncomingPayments.php';

/**
 * PlentymarketsExportEntityIncomingPayment provides the actual incoming payments export funcionality. Like the other export 
 * entities this class is called in PlentymarketsExportController. It is important to deliver a valid order ID
 * to the constructor method of this class.
 * The data export takes place based on plentymarkets SOAP-calls.
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsExportEntityIncomingPayment
{

	/**
	 * Shopware order data
	 *
	 * @var array
	 */
	protected $order = array();

	/**
	 * plentymarkets order data (out of shopware)
	 *
	 * @var array
	 */
	protected $plentyOrder = array();

	/**
	 *
	 * @param integer $orderID Shopware order id
	 * @throws Exception
	 */
	public function __construct($orderID)
	{
		$OrderResource = Shopware\Components\Api\Manager::getResource('Order');

		try
		{
			$this->order = $OrderResource->getOne($orderID);
		}
		catch (\Shopware\Components\Api\Exception\NotFoundException $E)
		{
			throw new Exception();
		}

		$Result = Shopware()->Db()->query('
			SELECT
					*
				FROM plenty_order
				WHERE shopwareId = ?
		', array(
			$orderID
		));

		$plentyOrder = $Result->fetchObject();
		if (!is_object($plentyOrder) || (integer) $plentyOrder->plentyOrderId <= 0)
		{
			PlentymarketsLogger::getInstance()->error('Sync:Order:IncomingPayment', 'The incoming payment could not be booked in plentymarkets because the sales order (' . $this->order['id'] . ') was not yet exported to plentymarkets.');
			throw new Exception();
		}
		if (!is_null($plentyOrder->plentyOrderPaidTimestamp))
		{
			PlentymarketsLogger::getInstance()->error('Sync:Order:IncomingPayment', 'The incoming payment of the sales order ' . $this->order['id'] . ' has already been exported to plentymarkets.');
			throw new Exception();
		}

		$this->plentyOrder = $plentyOrder;
	}

	/**
	 *
	 */
	public function book()
	{
		$Request_AddIncomingPayments = new PlentySoapRequest_AddIncomingPayments();

		$Request_AddIncomingPayments->IncomingPayments = array();
		$Object_AddIncomingPayments = new PlentySoapObject_AddIncomingPayments();
		$Object_AddIncomingPayments->Amount = $this->order['invoiceAmount'];
		$Object_AddIncomingPayments->Currency = PlentymarketsMappingController::getCurrencyByShopwareID($this->order['currency']);
		$Object_AddIncomingPayments->CustomerEmail = $this->order['customer']['email'];
		$Object_AddIncomingPayments->CustomerID = PlentymarketsMappingController::getCustomerByShopwareID($this->order['customerId']);
		$Object_AddIncomingPayments->CustomerName = $this->order['billing']['firstName'] . ' ' . $this->order['billing']['lastName'];
		$Object_AddIncomingPayments->MethodOfPaymentID = PlentymarketsMappingController::getMethodOfPaymentByShopwareID($this->order['paymentId']);
		$Object_AddIncomingPayments->OrderID = $this->plentyOrder->plentyOrderId;
		$Object_AddIncomingPayments->ReasonForPayment = sprintf('Shopware (OrderId: %u, CustomerId: %u)', $this->order['id'], $this->order['customerId']);

		if (empty($this->order['transactionId']))
		{
			$Object_AddIncomingPayments->TransactionID = $Object_AddIncomingPayments->ReasonForPayment;
		}
		else
		{
			$Object_AddIncomingPayments->TransactionID = $this->order['transactionId'];
		}

		if ($this->object['clearedDate'] instanceof DateTime)
		{
			$Object_AddIncomingPayments->TransactionTime = $this->order['clearedDate']->getTimestamp();
		}
		else
		{
			$Object_AddIncomingPayments->TransactionTime = time();
		}

		$Request_AddIncomingPayments->IncomingPayments[] = $Object_AddIncomingPayments;
		$Response_AddIncomingPayments = PlentymarketsSoapClient::getInstance()->AddIncomingPayments($Request_AddIncomingPayments);

		// Check for success
		if ($Response_AddIncomingPayments->Success)
		{
			PlentymarketsLogger::getInstance()->message('Sync:Order:IncomingPayment', 'The incoming payment of the sales order ' . $this->order['id'] . ' was booked in plentymarkets.');
			Shopware()->Db()->query('
					UPDATE plenty_order
						SET
							plentyOrderPaidStatus = 1,
							plentyOrderPaidTimestamp = NOW()
						WHERE shopwareId = ?
				', array(
				$this->order['id']
			));
		}
		else
		{
			PlentymarketsLogger::getInstance()->error('Sync:Order:IncomingPayment', 'The incoming payment of the sales order ' . $this->order['id'] . ' was not booked in plentymarkets.');
		}
	}
}
