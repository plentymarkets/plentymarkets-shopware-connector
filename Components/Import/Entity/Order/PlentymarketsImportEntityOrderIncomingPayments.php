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
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsImportEntityOrderIncomingPayments extends PlentymarketsImportEntityOrderAbstract
{
	/**
	 *
	 * @var integer
	 */
	protected $paymentStatusFull;

	/**
	 *
	 * @var integer
	 */
	protected $paymentStatusPartial;

	/**
	 *
	 * @var string
	 */
	protected static $action = 'IncomingPayments';

	public function prepare()
	{
		$timestamp = PlentymarketsConfig::getInstance()->getImportOrderIncomingPaymentsLastUpdateTimestamp(0);
		$this->log('LastUpdate: ' . date('r', $timestamp));

		$this->Request_SearchOrders->OrderPaidFrom = $timestamp;

		$this->paymentStatusFull = PlentymarketsConfig::getInstance()->getIncomingPaymentShopwarePaymentFullStatusID();
		$this->paymentStatusPartial = PlentymarketsConfig::getInstance()->getIncomingPaymentShopwarePaymentPartialStatusID();
	}

	public function finish()
	{
		PlentymarketsConfig::getInstance()->setImportOrderIncomingPaymentsLastUpdateTimestamp($this->timestamp);
	}

	/**
	 *
	 * @param integer $shopwareOrderId
	 * @param PlentySoapObject_OrderHead $Order
	 */
	public function handle($shopwareOrderId, $Order)
	{
		if ($Order->PaymentStatus == 1)
		{
			$paymentStatus = $this->paymentStatusFull;
		}
		else if ($Order->PaymentStatus == 4)
		{
			$paymentStatus = $this->paymentStatusPartial;
		}
		else
		{
			return;
		}

		self::$OrderModule->setPaymentStatus($shopwareOrderId, $paymentStatus, false, 'plentymarkets');

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
}
