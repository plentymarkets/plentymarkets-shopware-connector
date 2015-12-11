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
class PlentySoapObject_OrderHead
{
	
	/**
	 * @var string
	 */
	public $Currency;
	
	/**
	 * @var int
	 */
	public $CustomerID;
	
	/**
	 * @var string
	 */
	public $CustomerReference;
	
	/**
	 * @var int
	 */
	public $DeliveryAddressID;
	
	/**
	 * @var int
	 */
	public $DoneTimestamp;
	
	/**
	 * @var int
	 */
	public $DunningLevel;
	
	/**
	 * @var string
	 */
	public $EbaySellerAccount;
	
	/**
	 * @var string
	 */
	public $EstimatedTimeOfShipment;
	
	/**
	 * @var float
	 */
	public $ExchangeRatio;
	
	/**
	 * @var string
	 */
	public $ExternalOrderID;
	
	/**
	 * @var ArrayOfPlentysoapobject_orderincomingpayment
	 */
	public $IncomingPayments;
	
	/**
	 * @var float
	 */
	public $InitialCurrencyShippingCosts;
	
	/**
	 * @var float
	 */
	public $InitialCurrencyTotalInvoice;
	
	/**
	 * @var string
	 */
	public $Invoice;
	
	/**
	 * @var boolean
	 */
	public $IsNetto;
	
	/**
	 * @var int
	 */
	public $LastUpdate;
	
	/**
	 * @var int
	 */
	public $Marking1ID;
	
	/**
	 * @var int
	 */
	public $MethodOfPaymentID;
	
	/**
	 * @var PlentySoapObject_OrderDocumentNumbers
	 */
	public $OrderDocumentNumbers;
	
	/**
	 * @var int
	 */
	public $OrderID;
	
	/**
	 * @var ArrayOfPlentysoapobject_orderinfo
	 */
	public $OrderInfos;
	
	/**
	 * @var float
	 */
	public $OrderStatus;
	
	/**
	 * @var int
	 */
	public $OrderTimestamp;
	
	/**
	 * @var string
	 */
	public $OrderType;
	
	/**
	 * @var string
	 */
	public $PackageNumber;
	
	/**
	 * @var int
	 */
	public $PaidTimestamp;
	
	/**
	 * @var int
	 */
	public $ParentOrderID;
	
	/**
	 * @var int
	 */
	public $PaymentStatus;
	
	/**
	 * @var float
	 */
	public $ReferrerID;
	
	/**
	 * @var string
	 */
	public $RemoteIP;
	
	/**
	 * @var int
	 */
	public $ResponsibleID;
	
	/**
	 * @var int
	 */
	public $SalesAgentID;
	
	/**
	 * @var string
	 */
	public $SellerAccount;
	
	/**
	 * @var float
	 */
	public $ShippingCosts;
	
	/**
	 * @var int
	 */
	public $ShippingID;
	
	/**
	 * @var int
	 */
	public $ShippingMethodID;
	
	/**
	 * @var int
	 */
	public $ShippingProfileID;
	
	/**
	 * @var int
	 */
	public $StoreID;
	
	/**
	 * @var float
	 */
	public $TotalBrutto;
	
	/**
	 * @var float
	 */
	public $TotalInvoice;
	
	/**
	 * @var float
	 */
	public $TotalNetto;
	
	/**
	 * @var float
	 */
	public $TotalVAT;
	
	/**
	 * @var int
	 */
	public $WarehouseID;
}
