<?php

/**
 * plentymarkets shopware connector
 * Copyright © 2013-2015 plentymarkets GmbH
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
 * @copyright  Copyright (c) 2013-2015, plentymarkets GmbH (http://www.plentymarkets.com)
 * @author     Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */

/**
 * I am a generated class and am required for communicating with plentymarkets.
 */
class PlentySoapObject_GetIncomingPayments
{
	
	/**
	 * @var float
	 */
	public $Amount;
	
	/**
	 * @var string
	 */
	public $Currency;
	
	/**
	 * @var string
	 */
	public $CustomerEmail;
	
	/**
	 * @var int
	 */
	public $CustomerID;
	
	/**
	 * @var string
	 */
	public $CustomerName;
	
	/**
	 * @var int
	 */
	public $Date;
	
	/**
	 * @var string
	 */
	public $ExternalInvoiceNumber;
	
	/**
	 * @var int
	 */
	public $IncomingPaymentID;
	
	/**
	 * @var string
	 */
	public $InvoiceNumber;
	
	/**
	 * @var int
	 */
	public $MethodOfPaymentID;
	
	/**
	 * @var int
	 */
	public $OrderID;
	
	/**
	 * @var string
	 */
	public $ReasonForPayment;
	
	/**
	 * @var string
	 */
	public $TransactionID;
}
