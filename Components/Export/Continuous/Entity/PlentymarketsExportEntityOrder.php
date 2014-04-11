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
 * PlentymarketsExportEntityOrder provides the actual items export functionality. Like the other export
 * entities this class is called in PlentymarketsExportController. It is important to deliver valid
 * order ID to the constructor method of this class.
 * The data export takes place based on plentymarkets SOAP-calls.
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
	 * @var integer
	 */
	const CODE_ERROR_SOAP = 8;

	/**
	 *
	 * @var \Shopware\Models\Order\Order
	 */
	protected $Order;

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
	 * @var Shopware\Components\Api\Resource\Variant
	 */
	protected static $VariantApi;

	/**
	 * Constructor method
	 *
	 * @param unknown $orderID
	 * @throws PlentymarketsExportEntityException
	 */
	public function __construct($orderID)
	{
		$this->Order = Shopware()->Models()->find('Shopware\Models\Order\Order', $orderID);

		if (is_null($this->Order))
		{
			throw new PlentymarketsExportEntityException('The order with the id »' . $orderID . '« could not be exported (not found)', 4040);
		}
	}

	/**
	 * Flushes everything to /dev/null
	 */
	public function __destruct()
	{
		Shopware()->Models()->clear();
	}

	/**
	 * Exports the customer and the order respectively
	 */
	public function export()
	{
		$this->exportCustomer();
		$this->exportOrder();
	}

	/**
	 * Export the customer
	 */
	protected function exportCustomer()
	{
		$Customer = $this->Order->getCustomer();
		$Billing = $this->Order->getBilling();
		$Shipping = $this->Order->getShipping();

		//
		try
		{
			$PlentymarketsExportEntityOrderCustomer = new PlentymarketsExportEntityCustomer($Customer, $Billing, $Shipping);
			$PlentymarketsExportEntityOrderCustomer->export();
		}
		catch (PlentymarketsExportEntityException $E)
		{
			// Save the error
			$this->setError(self::CODE_ERROR_CUSTOMER);

			// Throw another exception
			throw new PlentymarketsExportEntityException('The order with the number »' . $this->Order->getNumber() . '« could not be exported (' . $E->getMessage() . ')', 4100);
		}

		//
		$this->PLENTY_customerID = $PlentymarketsExportEntityOrderCustomer->getPlentyCustomerID();
		$this->PLENTY_addressDispatchID = $PlentymarketsExportEntityOrderCustomer->getPlentyAddressDispatchID();
	}

	/**
	 * Export the order
	 */
	protected function exportOrder()
	{
		$VariantResource = self::getVariantApi();

		// Build the Request
		$Request_AddOrders = new PlentySoapRequest_AddOrders();
		$Request_AddOrders->Orders = array();

		//
		$Object_Order = new PlentySoapObject_Order();

		//
		$methodOfPayment = $this->getMethodOfPaymentId();
		if ($methodOfPayment == MOP_AMAZON_PAYMENT)
		{
			$externalOrderID = sprintf('Swag/%d/%s/%s', $this->Order->getId(), $this->Order->getNumber(), $this->Order->getTransactionId());
		}
		else
		{
			$externalOrderID = sprintf('Swag/%d/%s', $this->Order->getId(), $this->Order->getNumber());
		}

		$isOrderNet = (boolean) $this->Order->getNet() || (boolean) $this->Order->getTaxFree();

		// Order head
		$Object_OrderHead = new PlentySoapObject_OrderHead();
		$Object_OrderHead->Currency = PlentymarketsMappingController::getCurrencyByShopwareID($this->Order->getCurrency());
		$Object_OrderHead->CustomerID = $this->PLENTY_customerID;
		$Object_OrderHead->DeliveryAddressID = $this->PLENTY_addressDispatchID;
		$Object_OrderHead->ExternalOrderID = $externalOrderID;
		$Object_OrderHead->IsNetto = $isOrderNet;
		$Object_OrderHead->Marking1ID = PlentymarketsConfig::getInstance()->getOrderMarking1(null);
		$Object_OrderHead->MethodOfPaymentID = $this->getMethodOfPaymentId();
		$Object_OrderHead->OrderTimestamp = $this->getOrderTimestamp();
		$Object_OrderHead->OrderType = 'order';
		$Object_OrderHead->ResponsibleID = PlentymarketsConfig::getInstance()->getOrderUserID(null);
		$Object_OrderHead->ShippingCosts = $this->getShippingCosts();
		$Object_OrderHead->ShippingProfileID = $this->getParcelServicePresetId();
		$Object_OrderHead->StoreID = $this->getShopId();
		$Object_OrderHead->ReferrerID = $this->getReferrerId();

		$Object_Order->OrderHead = $Object_OrderHead;

		// Order infos
		$Object_OrderHead->OrderInfos = array();

		if ($Object_OrderHead->MethodOfPaymentID == MOP_DEBIT)
		{
			$Customer = $this->Order->getCustomer();

			if ($Customer)
			{
				$Debit = $Customer->getDebit();

				if ($Debit && $Debit->getAccountHolder())
				{
					$info = 'Account holder: ' . $Debit->getAccountHolder() . chr(10);
					$info .= 'Bank name: ' . $Debit->getBankName() . chr(10);
					$info .= 'Bank code: ' . $Debit->getBankCode() . chr(10);
					$info .= 'Account number: ' . $Debit->getAccount() . chr(10);

					$Object_OrderInfo = new PlentySoapObject_OrderInfo();
					$Object_OrderInfo->Info = $info;
					$Object_OrderInfo->InfoCustomer = 0;
					$Object_OrderInfo->InfoDate = $this->getOrderTimestamp();
					$Object_OrderHead->OrderInfos[] = $Object_OrderInfo;
				}
			}
		}

		if ($this->Order->getInternalComment())
		{
			$Object_OrderInfo = new PlentySoapObject_OrderInfo();
			$Object_OrderInfo->Info = $this->Order->getInternalComment();
			$Object_OrderInfo->InfoCustomer = 0;
			$Object_OrderInfo->InfoDate = $this->getOrderTimestamp();
			$Object_OrderHead->OrderInfos[] = $Object_OrderInfo;
		}

		if ($this->Order->getCustomerComment())
		{
			$Object_OrderInfo = new PlentySoapObject_OrderInfo();
			$Object_OrderInfo->Info = $this->Order->getCustomerComment();
			$Object_OrderInfo->InfoCustomer = 1;
			$Object_OrderInfo->InfoDate = $this->getOrderTimestamp();
			$Object_OrderHead->OrderInfos[] = $Object_OrderInfo;
		}

		if ($this->Order->getComment())
		{
			$Object_OrderInfo = new PlentySoapObject_OrderInfo();
			$Object_OrderInfo->Info = $this->Order->getComment();
			$Object_OrderInfo->InfoCustomer = 1;
			$Object_OrderInfo->InfoDate = $this->getOrderTimestamp();
			$Object_OrderHead->OrderInfos[] = $Object_OrderInfo;
		}

		$Object_Order->OrderItems = array();

		/** @var Shopware\Models\Order\Detail $Item */
		foreach ($this->Order->getDetails() as $Item)
		{
			// Variant
			try
			{
				$itemId = null;

				try
				{
					// get the detail id by the order number
					$articleDetailID = $VariantResource->getIdFromNumber($Item->getArticleNumber());
				}
				catch (Exception $E)
				{
					$articleDetailID = -1;
				}

				// get the sku from the detail id
				$sku = PlentymarketsMappingController::getItemVariantByShopwareID($articleDetailID);
			}

			catch (PlentymarketsMappingExceptionNotExistant $E)
			{
				// Base item
				try
				{
					$itemId = PlentymarketsMappingController::getItemByShopwareID($Item->getArticleId());
					$sku = null;
				}

					// Unknown item
				catch (PlentymarketsMappingExceptionNotExistant $E)
				{
					$itemId = -2;
					$sku = null;

					// Mandatory because there will be no mapping to any item
					$itemText = $Item->getArticleName();
				}
			}

			//
			if ($itemId > 0 || !empty($sku))
			{
				if (PlentymarketsConfig::getInstance()->getOrderItemTextSyncActionID(EXPORT_ORDER_ITEM_TEXT_SYNC) == EXPORT_ORDER_ITEM_TEXT_SYNC)
				{
					$itemText = $Item->getArticleName();
				}
				else
				{
					$itemText = null;
				}
			}

			// Coupon
			if ($Item->getMode() == 2)
			{
				$itemId = -1;
				$rowType = 'Coupon';
			}

			// surcharge for method of payment
			else
			{
				$number = $Item->getArticleNumber();

				// PAYONE fix
				if ($number == 'SHIPPING' && !$Object_OrderHead->ShippingCosts)
				{
					$Object_OrderHead->ShippingCosts = $Item->getPrice();
					continue;
				}

				$discountNumber = Shopware()->Config()->get('discountnumber');
				$surchargeNumber = Shopware()->Config()->get('surchargenumber');
				$paymentSurchargeNumber = Shopware()->Config()->get('paymentsurchargenumber');
				$paymentSurchargeAbsoluteNumber = Shopware()->Config()->get('paymentSurchargeAbsoluteNumber');
				$shippingDiscountNumber = Shopware()->Config()->get('shippingdiscountnumber');

				switch ($number)
				{
					case $paymentSurchargeNumber:
					case $paymentSurchargeAbsoluteNumber:
						$rowType = 'SurchargeForPaymentMethod';
						break;

					case $discountNumber:
						$rowType = 'Discount';
						break;

					case $surchargeNumber:
						$rowType = 'Surcharge';
						break;

					case $shippingDiscountNumber:
						$rowType = 'SurchargeForShippingMethod';
						break;

					default:
						$rowType = 'Default';
						break;
				}
			}

			if ($isOrderNet)
			{
				// Calculate the gross amount (needed by plentymakets even though it is a net sales order)
				$itemPrice = $Item->getPrice() * ((100 + (float) $Item->getTaxRate()) / 100);
			}
			else
			{
				$itemPrice = $Item->getPrice();
			}

			$Object_OrderItem = new PlentySoapObject_OrderItem();
			$Object_OrderItem->ExternalOrderItemID = $number;
			$Object_OrderItem->ItemID = $itemId;
			$Object_OrderItem->ReferrerID = $Object_OrderHead->ReferrerID;
			$Object_OrderItem->ItemText = $itemText;
			$Object_OrderItem->Price = $itemPrice;
			$Object_OrderItem->Quantity = $Item->getQuantity();
			$Object_OrderItem->SKU = $sku;
			$Object_OrderItem->VAT = $Item->getTaxRate();
			$Object_OrderItem->RowType = $rowType;

			$Object_Order->OrderItems[] = $Object_OrderItem;
		}

		$Request_AddOrders->Orders[] = $Object_Order;

		// Do the request
		$Response_AddOrders = PlentymarketsSoapClient::getInstance()->AddOrders($Request_AddOrders);

		if (!$Response_AddOrders->Success)
		{
			// Set the error end quit
			$this->setError(self::CODE_ERROR_SOAP);
			throw new PlentymarketsExportEntityException('The order with the number »' . $this->Order->getNumber() . '« could not be exported', 4010);
		}

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
		else
		{
			// Set the error end quit
			$this->setError(self::CODE_ERROR_SOAP);
			throw new PlentymarketsExportEntityException('The order with the number »' . $this->Order->getNumber() . '« could not be exported (no order id or order status respectively)', 4020);
		}

		// Directly book the incoming payment
		if ($this->Order->getPaymentStatus() && $this->Order->getPaymentStatus()->getId() == PlentymarketsConfig::getInstance()->getOrderPaidStatusID(12))
		{
			// May throw an exception
			$IncomingPayment = new PlentymarketsExportEntityOrderIncomingPayment($this->Order->getId());
			$IncomingPayment->book();
		}
	}

	/**
	 * Returns the order timestamp or the current timestamp if there is no order timestmap
	 *
	 * @return integer
	 */
	protected function getOrderTimestamp()
	{
		$OrderTime = $this->Order->getOrderTime();
		if ($OrderTime)
		{
			return $OrderTime->getTimestamp();
		}
		else
		{
			return time();
		}
	}

	/**
	 * Returns the parcel service preset id or null if there is neither mapping nor dispatch
	 *
	 * @return integer|null
	 */
	protected function getParcelServicePresetId()
	{
		// Sub-objects
		$Dispatch = $this->Order->getDispatch();

		// Shipping
		if ($Dispatch)
		{
			try
			{
				return PlentymarketsMappingController::getShippingProfileByShopwareID($Dispatch->getId());
			}
			catch (PlentymarketsMappingExceptionNotExistant $E)
			{
			}
		}

		return null;
	}

	/**
	 * Returns the method of payment id
	 *
	 * @throws PlentymarketsExportEntityException if there is no mapping
	 */
	protected function getMethodOfPaymentId()
	{
		// Sub-objects
		$Payment = $this->Order->getPayment();

		// Payment
		if ($Payment)
		{
			try
			{
				return PlentymarketsMappingController::getMethodOfPaymentByShopwareID($Payment->getId());
			}
			catch (PlentymarketsMappingExceptionNotExistant $E)
			{
				$shopgateIds = explode('|', PyConf()->get('OrderShopgateMOPIDs', ''));
				if (in_array($Payment->getId(), $shopgateIds))
				{
					return MOP_SHOPGATE;
				}
			}
		}

		// Save the error
		$this->setError(self::CODE_ERROR_MOP);

		// Exit
		throw new PlentymarketsExportEntityException('The order with the number »' . $this->Order->getNumber() . '« could not be exported (no mapping for method of payment)', 4030);
	}

	/**
	 * Returns the order referrer id
	 *
	 * @return integer
	 */
	protected function getReferrerId()
	{
		// Sub-objects
		$Partner = $this->Order->getPartner();

		// Referrer
		if ($Partner)
		{
			try
			{
				$referrerId = PlentymarketsMappingController::getReferrerByShopwareID($Partner->getId());
			}
			catch (PlentymarketsMappingExceptionNotExistant $E)
			{
			}
		}

		return isset($referrerId) ? $referrerId : PlentymarketsConfig::getInstance()->getOrderReferrerID();
	}

	/**
	 * Returns the shipping costs or null
	 *
	 * @return integer|null
	 */
	protected function getShippingCosts()
	{
		$isOrderNet = (boolean) $this->Order->getNet() || (boolean) $this->Order->getTaxFree();

		if ($this->Order->getInvoiceShipping() >= 0)
		{
			if (!$isOrderNet)
			{
				return $this->Order->getInvoiceShipping();
			}

			else
			{
				$taxRate = 0;

				// Use the highest tax rate
				if ($this->Order->getDispatch()->getTaxCalculation() == 0)
				{
					/** @var $detail Shopware\Models\Order\Detail */
					foreach ($this->Order->getDetails() as $detail)
					{
						$taxRate = max($taxRate, $detail->getTaxRate());
					}
				}
				else
				{
					$tax = Shopware()->Models()->find('Shopware\Models\Tax\Tax', $this->Order->getDispatch()->getTaxCalculation());
					if ($tax instanceof Shopware\Models\Tax\Tax)
					{
						$taxRate = $tax->getTax();
					}
				}

				if (!$taxRate)
				{
					return $this->Order->getDispatch()->getTaxCalculation();
				}
				else
				{
					return $this->Order->getInvoiceShipping() * ((100 + $taxRate) / 100);
				}
			}
		}

		return null;

	}

	/**
	 * Returns the shop id or null
	 *
	 * @return integer|null
	 */
	protected function getShopId()
	{
		// Sub-objects
		$Shop = $this->Order->getShop();

		// Shop
		if ($Shop)
		{
			try
			{
				return PlentymarketsMappingController::getShopByShopwareID($Shop->getId());
			}
			catch (PlentymarketsMappingExceptionNotExistant $E)
			{
			}
		}

		return null;
	}

	/**
	 * Writes an error code into the database
	 *
	 * @param integer $code
	 */
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
				$this->Order->getId()
			));
	}

	/**
	 * Writes the plenty order id and the status into the database
	 *
	 * @param integer $plentyOrderID
	 * @param float $plentyOrderStatus
	 */
	protected function setSuccess($plentyOrderID, $plentyOrderStatus)
	{
		PlentymarketsLogger::getInstance()->message('Export:Order', 'The sales order with the number  »' . $this->Order->getNumber() . '« has been created in plentymakets (id: ' . $plentyOrderID . ', status: ' . $plentyOrderStatus . ')');

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
				$this->Order->getId()
			));
	}

	/**
	 * Returns the Variant resource
	 *
	 * @return \Shopware\Components\Api\Resource\Variant
	 */
	protected static function getVariantApi()
	{
		if (is_null(self::$VariantApi))
		{
			self::$VariantApi = Shopware\Components\Api\Manager::getResource('Variant');
		}

		return self::$VariantApi;
	}
}
