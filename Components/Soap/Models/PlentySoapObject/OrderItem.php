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
class PlentySoapObject_OrderItem
{
    /**
     * @var int
     */
    public $BundleItemID;

    /**
     * @var string
     */
    public $Currency;

    /**
     * @var float
     */
    public $CurrencyGross;

    /**
     * @var float
     */
    public $CurrencyNet;

    /**
     * @var string
     */
    public $EAN;

    /**
     * @var string
     */
    public $EAN2;

    /**
     * @var string
     */
    public $EAN3;

    /**
     * @var string
     */
    public $EAN4;

    /**
     * @var string
     */
    public $ExternalItemID;

    /**
     * @var string
     */
    public $ExternalOrderItemID;

    /**
     * @var int
     */
    public $ItemID;

    /**
     * @var string
     */
    public $ItemNo;

    /**
     * @var float
     */
    public $ItemRebate;

    /**
     * @var string
     */
    public $ItemText;

    /**
     * @var string
     */
    public $NeckermannItemNo;

    /**
     * @var int
     */
    public $OrderID;

    /**
     * @var int
     */
    public $OrderRowID;

    /**
     * @var int
     */
    public $ParentOrderRowID;

    /**
     * @var float
     */
    public $Price;

    /**
     * @var float
     */
    public $Quantity;

    /**
     * @var float
     */
    public $ReferrerID;

    /**
     * @var PlentySoapEnumeration_OrderItemRowType
     */
    public $RowType;

    /**
     * @var string
     */
    public $SKU;

    /**
     * @var ArrayOfPlentysoapobject_salesorderproperty
     */
    public $SalesOrderProperties;

    /**
     * @var ArrayOfPlentysoapobject_integer
     */
    public $StorageLocationIDs;

    /**
     * @var float
     */
    public $VAT;

    /**
     * @var int
     */
    public $WarehouseID;
}
