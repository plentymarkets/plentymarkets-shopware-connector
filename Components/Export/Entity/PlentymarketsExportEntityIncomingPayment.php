<?php
require_once __DIR__ . '/../../Soap/Models/PlentySoapObject/AddIncomingPayments.php';
require_once __DIR__ . '/../../Soap/Models/PlentySoapRequest/AddIncomingPayments.php';

/**
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
			PlentymarketsLogger::getInstance()->error('Sync:Order:Payment', 'The incoming payment could not be booked in plentymarkets because the sales order (' . $this->order['id'] . ') was not yet exported to plentymarkets.');
			throw new Exception();
		}

		if ((integer) $plentyOrder->plentyOrderPaidTimestamp > 0)
		{
			PlentymarketsLogger::getInstance()->error('Sync:Order:Payment', 'The incoming payment of the sales order ' . $this->order['id'] . ' has already been exported to plentymarkets.');
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
			PlentymarketsLogger::getInstance()->message('Sync:Order:Payment', 'The incoming payment of the sales order ' . $this->order['id'] . ' was booked in plentymarkets.');
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
			PlentymarketsLogger::getInstance()->error('Sync:Order:Payment', 'The incoming payment of the sales order ' . $this->order['id'] . ' was not booked in plentymarkets.');
		}
	}
}
