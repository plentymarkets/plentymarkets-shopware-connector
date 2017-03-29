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
class PlentySoapObject_GetShippingProfiles
{
    /**
     * @var string
     */
    public $BackendName;

    /**
     * @var int
     */
    public $Category;

    /**
     * @var int
     */
    public $DefaultEnabled;

    /**
     * @var int
     */
    public $EbayAuctionTyp;

    /**
     * @var int
     */
    public $EbayExpressShipping;

    /**
     * @var string
     */
    public $EbayShippingProfiles;

    /**
     * @var string
     */
    public $ExcludedCustomerGroups;

    /**
     * @var string
     */
    public $ExcludedMethodOfPayments;

    /**
     * @var float
     */
    public $ExtraChargeForIslands;

    /**
     * @var int
     */
    public $ItemExtraShippingCharge;

    /**
     * @var int
     */
    public $MarkingID;

    /**
     * @var string
     */
    public $Multishops;

    /**
     * @var int
     */
    public $NeckermannReferenceID;

    /**
     * @var int
     */
    public $PostIdent;

    /**
     * @var int
     */
    public $Priority;

    /**
     * @var string
     */
    public $SalesOrderReferrers;

    /**
     * @var ArrayOfPlentysoapobject_shippingcharges
     */
    public $ShippingCharges;

    /**
     * @var string
     */
    public $ShippingGroups;

    /**
     * @var int
     */
    public $ShippingProfileID;

    /**
     * @var int
     */
    public $ShippingServiceProviderID;

    /**
     * @var string
     */
    public $WebshopName;
}
