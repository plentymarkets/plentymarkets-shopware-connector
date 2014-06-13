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
 * Handles the export of the incoming payments
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsExportContinuousControllerOrderIncomingPayment
{
	/**
	 * Runs the export of the incoming payments
	 */
	public function run()
	{

		// Start
		PlentymarketsConfig::getInstance()->setItemIncomingPaymentExportStart(time());

		$now = time();
		$lastUpdateTimestamp = date('Y-m-d H:i:s', PlentymarketsConfig::getInstance()->getItemIncomingPaymentExportLastUpdate(time()));
		$status = explode('|', PlentymarketsConfig::getInstance()->getOrderPaidStatusID(12));
		$status = array_map('intval', $status);

		if (!count($status))
		{
			$status = array(12);
		}


		$Result = Shopware()->Db()->query('
			SELECT
					DISTINCT orderID
				FROM s_order_history
					JOIN plenty_order ON shopwareId = orderID
				WHERE
					change_date > \'' . $lastUpdateTimestamp . '\' AND
					payment_status_id IN ' . implode(', ', $status) . ' AND
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
				PlentymarketsLogger::getInstance()->error('Sync:Order:IncomingPayment', $E->getMessage(), $E->getCode());
			}
		}

		// Set timestamps
		PlentymarketsConfig::getInstance()->setItemIncomingPaymentExportTimestampFinished(time());
		PlentymarketsConfig::getInstance()->setItemIncomingPaymentExportLastUpdate($now);
	}
}
