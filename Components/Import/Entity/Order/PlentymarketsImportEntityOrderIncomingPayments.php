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
 * PlentymarketsImportEntityOrderIncomingPayments provides
 * the actual order incoming payments import functionality
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsImportEntityOrderIncomingPayments extends PlentymarketsImportEntityOrderAbstract
{
	/**
	 *
	 * @var integer
	 */
	protected static $paymentStatusFull;

	/**
	 *
	 * @var integer
	 */
	protected static $paymentStatusPartial;

	/**
	 *
	 * @var string
	 */
	protected static $action = 'IncomingPayment';

	/**
	 * Prepares the soap orders SOAP object
	 *
	 * @see PlentymarketsImportEntityOrderAbstract::prepare()
	 */
	public function prepare()
	{
		$timestamp = PlentymarketsConfig::getInstance()->getImportOrderIncomingPaymentsLastUpdateTimestamp(0);
		if ($timestamp > 0)
		{
			$this->log('LastUpdate: ' . date('r', $timestamp));
		}

		$this->Request_SearchOrders->OrderPaidFrom = $timestamp;

		if (is_null(self::$paymentStatusFull))
		{
			self::$paymentStatusFull = PlentymarketsConfig::getInstance()->getIncomingPaymentShopwarePaymentFullStatusID();
		}

		if (is_null(self::$paymentStatusPartial))
		{
			self::$paymentStatusPartial = PlentymarketsConfig::getInstance()->getIncomingPaymentShopwarePaymentPartialStatusID();
		}
	}

	/**
	 * Handles the actual import
	 *
	 * @param integer $shopwareOrderId
	 * @param PlentySoapObject_OrderHead $Order
	 */
	public function handle($shopwareOrderId, $Order)
	{
		// Payment status
		if ($Order->PaymentStatus == 1)
		{
			$paymentStatus = self::$paymentStatusFull;
		}
		else if ($Order->PaymentStatus == 4)
		{
			$paymentStatus = self::$paymentStatusPartial;
		}
		else
		{
			$paymentStatus = -1;
		}

		if ($paymentStatus > 0)
		{
			self::$OrderModule->setPaymentStatus($shopwareOrderId, $paymentStatus, false, 'plentymarkets');

			Shopware()->Db()->query('
				UPDATE plenty_order
					SET
						plentyOrderPaidStatus = 1,
						plentyOrderPaidTimestamp = NOW()
					WHERE shopwareId = ?
			', array(
				$shopwareOrderId,
			));
		}
	}
}
