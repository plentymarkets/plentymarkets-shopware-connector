<?php
require_once PY_COMPONENTS . 'Export/Continuous/Entity/PlentymarketsExportEntityOrderIncomingPayment.php';

class PlentymarketsExportContinuousControllerOrderIncomingPayment
{

	public function run()
	{

		// Start
		PlentymarketsConfig::getInstance()->setItemIncomingPaymentExportStart(time());

		$now = time();
		$lastUpdateTimestamp = date('Y-m-d H:i:s', PlentymarketsConfig::getInstance()->getItemIncomingPaymentExportLastUpdate(time()));
		$status = PlentymarketsConfig::getInstance()->getOrderPaidStatusID(12);

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
				$ExportEntityIncomingPayment = new PlentymarketsExportEntityOrderIncomingPayment($order->orderID);
				$ExportEntityIncomingPayment->book();
			}
			catch (PlentymarketsExportEntityException $E)
			{
				PlentymarketsLogger::getInstance()->error('Sync:Order:IncomingPayment', $E->getMessage());
			}
		}

		// Set running
		PlentymarketsConfig::getInstance()->setItemIncomingPaymentExportTimestampFinished(time());
		PlentymarketsConfig::getInstance()->setItemIncomingPaymentExportLastUpdate($now);
	}
}
