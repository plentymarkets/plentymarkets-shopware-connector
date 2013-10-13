<?php
require_once PY_COMPONENTS . 'Export/Continuous/Entity/PlentymarketsExportEntityOrder.php';

class PlentymarketsExportContinuousControllerOrder
{

	public function run()
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
				$PlentymarketsExportEntityOrder = new PlentymarketsExportEntityOrder($Order->shopwareId);
				$PlentymarketsExportEntityOrder->export();
			}
			catch (PlentymarketsExportEntityException $E)
			{
				PlentymarketsLogger::getInstance()->error('Export:Order', $E->getMessage());
			}
		}
	}
}
