<?php
require_once __DIR__ . '/../../Soap/Models/PlentySoapObject/DeliveryAddress.php';
require_once __DIR__ . '/../../Soap/Models/PlentySoapObject/Order.php';
require_once __DIR__ . '/../../Soap/Models/PlentySoapObject/OrderDocumentNumbers.php';
require_once __DIR__ . '/../../Soap/Models/PlentySoapObject/OrderHead.php';
require_once __DIR__ . '/../../Soap/Models/PlentySoapObject/OrderIncomingPayment.php';
require_once __DIR__ . '/../../Soap/Models/PlentySoapObject/OrderInfo.php';
require_once __DIR__ . '/../../Soap/Models/PlentySoapObject/OrderItem.php';
require_once __DIR__ . '/../../Soap/Models/PlentySoapObject/SalesOrderProperty.php';
require_once __DIR__ . '/../../Soap/Models/PlentySoapObject/String.php';
require_once __DIR__ . '/../../Soap/Models/PlentySoapRequest/AddOrders.php';

require_once __DIR__ . '/PlentymarketsExportEntityCustomer.php';
require_once __DIR__ . '/PlentymarketsExportEntityIncomingPayment.php';

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
		//
		$PlentymarketsExportEntityCustomer = new PlentymarketsExportEntityCustomer($this->order['customer'], $this->order['billing'], $this->order['shipping']);
		$PlentymarketsExportEntityCustomer->export();

		//
		$this->PLENTY_customerID = $PlentymarketsExportEntityCustomer->getPlentyCustomerID();

		// Customer could not be created
		if ((integer) $this->PLENTY_customerID <= 0)
		{
			// Save the error
			$this->setError(self::CODE_ERROR_CUSTOMER);

			// abort
			return false;
		}

		//
		$this->PLENTY_addressDispatchID = $PlentymarketsExportEntityCustomer->getPlentyAddressDispatchID();

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
		$Object_OrderHead->WebstoreID = PlentymarketsConfig::getInstance()->getWebstoreID(0); // int

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
