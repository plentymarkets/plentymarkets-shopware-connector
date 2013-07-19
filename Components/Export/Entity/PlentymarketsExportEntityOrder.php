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

require_once PY_SOAP . 'Models/PlentySoapObject/DeliveryAddress.php';
require_once PY_SOAP . 'Models/PlentySoapObject/Order.php';
require_once PY_SOAP . 'Models/PlentySoapObject/OrderDocumentNumbers.php';
require_once PY_SOAP . 'Models/PlentySoapObject/OrderHead.php';
require_once PY_SOAP . 'Models/PlentySoapObject/OrderIncomingPayment.php';
require_once PY_SOAP . 'Models/PlentySoapObject/OrderInfo.php';
require_once PY_SOAP . 'Models/PlentySoapObject/OrderItem.php';
require_once PY_SOAP . 'Models/PlentySoapObject/SalesOrderProperty.php';
require_once PY_SOAP . 'Models/PlentySoapObject/String.php';
require_once PY_SOAP . 'Models/PlentySoapRequest/AddOrders.php';
require_once PY_COMPONENTS . 'Export/Entity/PlentymarketsExportEntityCustomer.php';
require_once PY_COMPONENTS . 'Export/Entity/PlentymarketsExportEntityIncomingPayment.php';

/**
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsExportEntityOrder
{

	/**
	 *
	 * @var integer
	 */
	const CODE_SUCCESS = 2;

	/**
	 *
	 * @var integer
	 */
	const CODE_ERROR_CUSTOMER = 1;

	/**
	 *
	 * @var integer
	 */
	const CODE_ERROR_MOP = 4;

	/**
	 *
	 * @var PDOStatement
	 */
	protected static $StatementGetSKU = null;

	/**
	 *
	 * @var array
	 */
	protected $order;

	/**
	 *
	 * @var integer
	 */
	protected $PLENTY_customerID;

	/**
	 *
	 * @var integer|null
	 */
	protected $PLENTY_addressDispatchID;

	/**
	 *
	 * @param unknown $orderID
	 */
	public function __construct($orderID)
	{
		$OrderResource = \Shopware\Components\Api\Manager::getResource('Order');
		try
		{
			$this->order = $OrderResource->getOne($orderID);
		}
		catch (\Shopware\Components\Api\Exception\NotFoundException $E)
		{
		}

		if (is_null(self::$StatementGetSKU))
		{
			self::$StatementGetSKU = Shopware()->Db()->prepare('
				SELECT kind, id detailsID, articleID
					FROM s_articles_details
					WHERE ordernumber = ?
					LIMIT 1
			');
		}
	}

	/**
	 * Exports the customer and the order respectively
	 */
	public function export()
	{
		if (!$this->exportCustomer())
		{
			return;
		}
		$this->exportOrder();
	}

	/**
	 * Export the customer
	 *
	 * @return boolean
	 */
	protected function exportCustomer()
	{
		$Customer = Shopware()->Models()
			->getRepository('Shopware\Models\Customer\Customer')
			->find($this->order['customer']['id']);

		$Billing = Shopware()->Models()
			->getRepository('Shopware\Models\Order\Billing')
			->find($this->order['billing']['id']);

		if (!is_null($this->order['shipping']))
		{
			$Shipping = Shopware()->Models()
				->getRepository('Shopware\Models\Order\Shipping')
				->find($this->order['shipping']['id']);
		}
		else
		{
			$Shipping = null;
		}

		//
		$PlentymarketsExportEntityOrderCustomer = new PlentymarketsExportEntityCustomer($Customer, $Billing, $Shipping);
		$PlentymarketsExportEntityOrderCustomer->export();

		//
		$this->PLENTY_customerID = $PlentymarketsExportEntityOrderCustomer->getPlentyCustomerID();

		// Customer could not be created
		if ((integer) $this->PLENTY_customerID <= 0)
		{
			// Save the error
			$this->setError(self::CODE_ERROR_CUSTOMER);

			// abort
			return false;
		}

		//
		$this->PLENTY_addressDispatchID = $PlentymarketsExportEntityOrderCustomer->getPlentyAddressDispatchID();

		// success
		return true;
	}

	/**
	 * Export the order
	 */
	protected function exportOrder()
	{
		// Mapping für Versand
		try
		{
			list ($parcelServicePresetID, $parcelServiceID) = explode(';', PlentymarketsMappingController::getShippingProfileByShopwareID($this->order['dispatchId']));
		}
		catch (PlentymarketsMappingExceptionNotExistant $E)
		{
			$parcelServicePresetID = null;
			$parcelServiceID = null;
		}

		try
		{
			$methodOfPaymentId = PlentymarketsMappingController::getMethodOfPaymentByShopwareID($this->order['payment']['id']);
		}
		catch (PlentymarketsMappingExceptionNotExistant $E)
		{
			return $this->setError(self::CODE_ERROR_MOP);
		}

		// Shipping costs
		$shippingCosts = $this->order['invoiceShipping'] > 0 ? $this->order['invoiceShipping'] : null;

		// Build the Request
		$Request_AddOrders = new PlentySoapRequest_AddOrders();
		$Request_AddOrders->Orders = array();

		//
		$Object_Order = new PlentySoapObject_Order();

		$Object_OrderHead = new PlentySoapObject_OrderHead();
		$Object_OrderHead->Currency = PlentymarketsMappingController::getCurrencyByShopwareID($this->order['currency']);
		$Object_OrderHead->CustomerID = $this->PLENTY_customerID; // int
		$Object_OrderHead->DeliveryAddressID = $this->PLENTY_addressDispatchID; // int
		$Object_OrderHead->DoneTimestamp = null; // string
		$Object_OrderHead->DunningLevel = null; // int
		$Object_OrderHead->EbaySellerAccount = null; // string
		$Object_OrderHead->EstimatedTimeOfShipment = null; // string
		$Object_OrderHead->ExchangeRatio = null; // float
		$Object_OrderHead->ExternalOrderID = PlentymarketsUtils::getExternalCustomerID($this->order['id']); // string
		$Object_OrderHead->IsNetto = false; // boolean
		$Object_OrderHead->Marking1ID = PlentymarketsConfig::getInstance()->getOrderMarking1(0); // int
		$Object_OrderHead->MethodOfPaymentID = $methodOfPaymentId; // int
		$Object_OrderHead->StoreID = PlentymarketsConfig::getInstance()->getStoreID(0); // int

		// $Object_OrderHead->OrderStatus = 3; // float
		$Object_OrderHead->OrderTimestamp = $this->order['orderTime']->getTimestamp(); // int
		$Object_OrderHead->OrderType = 'order'; // string
		$Object_OrderHead->PackageNumber = null; // string


		$Object_OrderHead->ParentOrderID = null; // int
		$Object_OrderHead->ReferrerID = PlentymarketsConfig::getInstance()->getOrderReferrerID(0); // int
		$Object_OrderHead->ResponsibleID = PlentymarketsConfig::getInstance()->getOrderUserID(null); // int
		$Object_OrderHead->ShippingCosts = $shippingCosts; // float
		$Object_OrderHead->ShippingMethodID = $parcelServiceID; // int
		$Object_OrderHead->ShippingProfileID = $parcelServicePresetID; // int
		$Object_Order->OrderHead = $Object_OrderHead;

		$Object_Order->OrderItems = array();

		foreach ($this->order['details'] as $item)
		{
			self::$StatementGetSKU->execute(array(
				$item['articleNumber']
			));

			// Fetch the item
			$sw_OrderItem = self::$StatementGetSKU->fetchObject();

			try
			{
				// Variant
				if ($sw_OrderItem->kind == 2)
				{
					$itemId = null;
					$sku = PlentymarketsMappingController::getItemVariantByShopwareID($sw_OrderItem->detailsID);
				}

				// Base item
				else
				{
					$itemId = PlentymarketsMappingController::getItemByShopwareID($sw_OrderItem->articleID);
					$sku = null;
				}
			}

			// neither one
			catch (PlentymarketsMappingExceptionNotExistant $E)
			{
				$itemId = -2;
				$sku = null;
			}

			$Object_OrderItem = new PlentySoapObject_OrderItem();
			$Object_OrderItem->ExternalOrderItemID = $item['articleNumber']; // string
			$Object_OrderItem->ItemID = $itemId; // int
			$Object_OrderItem->ItemText = $item['articleName']; // string
			$Object_OrderItem->Price = $item['price']; // float
			$Object_OrderItem->Quantity = $item['quantity']; // float
			$Object_OrderItem->SKU = $sku; // string

			$Object_OrderItem->VAT = null; // float
			$Object_OrderItem->WarehouseID = null; // int
			$Object_Order->OrderItems[] = $Object_OrderItem;
		}

		$Request_AddOrders->Orders[] = $Object_Order;

		// Do the request
		$Response_AddOrders = PlentymarketsSoapClient::getInstance()->AddOrders($Request_AddOrders);

		//
		$plentyOrderID = null;
		$plentyOrderStatus = 0.00;

		foreach ($Response_AddOrders->ResponseMessages->item[0]->SuccessMessages->item as $SuccessMessage)
		{
			switch ($SuccessMessage->Key)
			{
				case 'OrderID':
					$plentyOrderID = (integer) $SuccessMessage->Value;
					break;

				case 'Status':
					$plentyOrderStatus = (float) $SuccessMessage->Value;
					break;
			}
		}

		if ($plentyOrderID && $plentyOrderStatus)
		{
			$this->setSuccess($plentyOrderID, $plentyOrderStatus);
		}

		// Directly book the incomming payment
		if ($this->order['paymentStatus']['id'] == PlentymarketsConfig::getInstance()->getOrderPaidStatusID(12))
		{
			try
			{
				$IncomingPayment = new PlentymarketsExportEntityIncomingPayment($this->order['id']);
				$IncomingPayment->book();
			}
			catch (Exception $e)
			{
			}
		}

		// outgoing items?
	}

	protected function setError($code)
	{
		Shopware()->Db()
			->prepare('
			UPDATE plenty_order
				SET
					status = ?,
					timestampLastTry = NOW(),
					numberOfTries = numberOfTries + 1
				WHERE shopwareId = ?
		')
			->execute(array(
			$code,
			$this->order['id']
		));
	}

	protected function setSuccess($plentyOrderID, $plentyOrderStatus)
	{
		PlentymarketsLogger::getInstance()->message('Export:Order', 'The sales order with the id ' . $this->order['id'] . ' has been created in plentymakets (id: ' . $plentyOrderID . ', status: ' . $plentyOrderStatus . ')');

		Shopware()->Db()
			->prepare('
			UPDATE plenty_order
				SET
					status = 2,
					timestampLastTry = NOW(),
					numberOfTries = numberOfTries + 1,
					plentyOrderTimestamp = NOW(),
					plentyOrderId = ?,
					plentyOrderStatus = ?
				WHERE shopwareId = ?
		')
			->execute(array(
			$plentyOrderID,
			$plentyOrderStatus,
			$this->order['id']
		));
	}
}
