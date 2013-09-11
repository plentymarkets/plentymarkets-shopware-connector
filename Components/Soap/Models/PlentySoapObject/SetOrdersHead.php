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
 * I am a generated class and am required for communicating with plentymarkets.
 */
class PlentySoapObject_SetOrdersHead
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
	 * @var int
	 */
	public $DeliveryAddressID;
	
	/**
	 * @var string
	 */
	public $DoneTimestamp;
	
	/**
	 * @var string
	 */
	public $EstimatedTimeOfShipment;
	
	/**
	 * @var string
	 */
	public $ExternalOrderID;
	
	/**
	 * @var int
	 */
	public $Marking1ID;
	
	/**
	 * @var int
	 */
	public $MethodOfPaymentID;
	
	/**
	 * @var int
	 */
	public $StoreID;
	
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
	 * @var string
	 */
	public $PaidTimestamp;
	
	/**
	 * @var int
	 */
	public $ReferrerID;
	
	/**
	 * @var int
	 */
	public $ResponsibleID;
	
	/**
	 * @var int
	 */
	public $SalesAgentID;
	
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
	public $WarehouseID;
}
