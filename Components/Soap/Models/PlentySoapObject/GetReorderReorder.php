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
 */
class PlentySoapObject_GetReorderReorder
{
	
	/**
	 * @var int
	 */
	public $DeliveryTimestamp;
	
	/**
	 * @var int
	 */
	public $MarkingID;
	
	/**
	 * @var int
	 */
	public $OrderTimestamp;
	
	/**
	 * @var int
	 */
	public $PaymentTimestamp;
	
	/**
	 * @var int
	 */
	public $ReorderID;
	
	/**
	 * @var ArrayOfPlentysoapobject_getreorderitem
	 */
	public $ReorderItems;
	
	/**
	 * @var float
	 */
	public $ReorderStatus;
	
	/**
	 * @var int
	 */
	public $ResponsibleID;
	
	/**
	 * @var int
	 */
	public $SupplierID;
	
	/**
	 * @var string
	 */
	public $SupplierSign;
	
	/**
	 * @var float
	 */
	public $Total;
	
	/**
	 * @var int
	 */
	public $WarehouseID;
}
