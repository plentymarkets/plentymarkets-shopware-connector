<?php
/**
 * plentymarkets shopware connector
 * Copyright © 2013-2014 plentymarkets GmbH
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
 * @copyright  Copyright (c) 2014, plentymarkets GmbH (http://www.plentymarkets.com)
 * @author     Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */

/**
 * I am a generated class and am required for communicating with plentymarkets.
 */
class PlentySoapRequest_AddAuction
{
	
	/**
	 * @var string
	 */
	public $AddBasePrice;
	
	/**
	 * @var int
	 */
	public $AddCompatibilityList;
	
	/**
	 * @var string
	 */
	public $ArticleEanIsbn;
	
	/**
	 * @var int
	 */
	public $AuctionID;
	
	/**
	 * @var int
	 */
	public $AutoBestOffer;
	
	/**
	 * @var int
	 */
	public $AutoRelist;
	
	/**
	 * @var int
	 */
	public $AutoSelling;
	
	/**
	 * @var int
	 */
	public $BundleQuantity;
	
	/**
	 * @var float
	 */
	public $BuyItNowPrice;
	
	/**
	 * @var string
	 */
	public $CheckoutInstructions;
	
	/**
	 * @var int
	 */
	public $ConditionID;
	
	/**
	 * @var int
	 */
	public $Counter;
	
	/**
	 * @var string
	 */
	public $Country;
	
	/**
	 * @var int
	 */
	public $DispatchTimeMax;
	
	/**
	 * @var string
	 */
	public $Duration;
	
	/**
	 * @var int
	 */
	public $EbayCategory1;
	
	/**
	 * @var int
	 */
	public $EbayCategory2;
	
	/**
	 * @var float
	 */
	public $EbayFee;
	
	/**
	 * @var int
	 */
	public $EbayIntRateTable;
	
	/**
	 * @var int
	 */
	public $EbayIntShippingService1;
	
	/**
	 * @var int
	 */
	public $EbayIntShippingService2;
	
	/**
	 * @var int
	 */
	public $EbayIntShippingService3;
	
	/**
	 * @var int
	 */
	public $EbayIntShippingService4;
	
	/**
	 * @var int
	 */
	public $EbayIntShippingService5;
	
	/**
	 * @var float
	 */
	public $EbayIntShippingServiceAdditionalCost1;
	
	/**
	 * @var float
	 */
	public $EbayIntShippingServiceAdditionalCost2;
	
	/**
	 * @var float
	 */
	public $EbayIntShippingServiceAdditionalCost3;
	
	/**
	 * @var float
	 */
	public $EbayIntShippingServiceAdditionalCost4;
	
	/**
	 * @var float
	 */
	public $EbayIntShippingServiceAdditionalCost5;
	
	/**
	 * @var float
	 */
	public $EbayIntShippingServiceCost1;
	
	/**
	 * @var float
	 */
	public $EbayIntShippingServiceCost2;
	
	/**
	 * @var float
	 */
	public $EbayIntShippingServiceCost3;
	
	/**
	 * @var float
	 */
	public $EbayIntShippingServiceCost4;
	
	/**
	 * @var float
	 */
	public $EbayIntShippingServiceCost5;
	
	/**
	 * @var string
	 */
	public $EbayIntShippingServiceLocations4;
	
	/**
	 * @var string
	 */
	public $EbayIntShippingServiceLocations5;
	
	/**
	 * @var string
	 */
	public $EbayListingEnhancements;
	
	/**
	 * @var int
	 */
	public $EbayNatRateTable;
	
	/**
	 * @var int
	 */
	public $EbayNatShippingService1;
	
	/**
	 * @var int
	 */
	public $EbayNatShippingService2;
	
	/**
	 * @var int
	 */
	public $EbayNatShippingService3;
	
	/**
	 * @var int
	 */
	public $EbayNatShippingService4;
	
	/**
	 * @var float
	 */
	public $EbayNatShippingServiceAdditionalCost1;
	
	/**
	 * @var float
	 */
	public $EbayNatShippingServiceAdditionalCost2;
	
	/**
	 * @var float
	 */
	public $EbayNatShippingServiceAdditionalCost3;
	
	/**
	 * @var float
	 */
	public $EbayNatShippingServiceAdditionalCost4;
	
	/**
	 * @var float
	 */
	public $EbayNatShippingServiceCost1;
	
	/**
	 * @var float
	 */
	public $EbayNatShippingServiceCost2;
	
	/**
	 * @var float
	 */
	public $EbayNatShippingServiceCost3;
	
	/**
	 * @var float
	 */
	public $EbayNatShippingServiceCost4;
	
	/**
	 * @var string
	 */
	public $EbayPaymentMethods;
	
	/**
	 * @var int
	 */
	public $EbaySite;
	
	/**
	 * @var int
	 */
	public $EbayTemplateID;
	
	/**
	 * @var int
	 */
	public $EbayVerify;
	
	/**
	 * @var string
	 */
	public $EbayVerifyErr;
	
	/**
	 * @var string
	 */
	public $Ebayuser;
	
	/**
	 * @var int
	 */
	public $Gallery;
	
	/**
	 * @var string
	 */
	public $Galleryurl;
	
	/**
	 * @var int
	 */
	public $GetItFast;
	
	/**
	 * @var int
	 */
	public $InternationalPromotionalShippingDiscount;
	
	/**
	 * @var int
	 */
	public $InternationalShippingDiscountProfileID;
	
	/**
	 * @var int
	 */
	public $ItemQuantity;
	
	/**
	 * @var string
	 */
	public $Lang;
	
	/**
	 * @var string
	 */
	public $LastList;
	
	/**
	 * @var string
	 */
	public $LastUpdate;
	
	/**
	 * @var int
	 */
	public $ListEbay;
	
	/**
	 * @var int
	 */
	public $ListRicardo;
	
	/**
	 * @var int
	 */
	public $ListVariations;
	
	/**
	 * @var int
	 */
	public $ListingDistance;
	
	/**
	 * @var string
	 */
	public $Location;
	
	/**
	 * @var string
	 */
	public $LongDescription;
	
	/**
	 * @var int
	 */
	public $MaxParallelLiveAuctions;
	
	/**
	 * @var float
	 */
	public $MinimumBid;
	
	/**
	 * @var string
	 */
	public $PaypalEmail;
	
	/**
	 * @var string
	 */
	public $PostalCode;
	
	/**
	 * @var int
	 */
	public $PrivateAuction;
	
	/**
	 * @var int
	 */
	public $PromotionalShippingDiscount;
	
	/**
	 * @var float
	 */
	public $ReservePrice;
	
	/**
	 * @var int
	 */
	public $RicardoAvailability;
	
	/**
	 * @var int
	 */
	public $RicardoCategory;
	
	/**
	 * @var float
	 */
	public $RicardoFee;
	
	/**
	 * @var int
	 */
	public $RicardoPaymentcondition;
	
	/**
	 * @var string
	 */
	public $RicardoPaymentmethods;
	
	/**
	 * @var float
	 */
	public $RicardoPriceIncrement;
	
	/**
	 * @var string
	 */
	public $RicardoPromotionFlags;
	
	/**
	 * @var int
	 */
	public $RicardoShippingNational;
	
	/**
	 * @var float
	 */
	public $RicardoShippingNationalCosts;
	
	/**
	 * @var int
	 */
	public $RicardoSite;
	
	/**
	 * @var int
	 */
	public $RicardoState;
	
	/**
	 * @var int
	 */
	public $RicardoTemplateID;
	
	/**
	 * @var string
	 */
	public $RicardoUser;
	
	/**
	 * @var int
	 */
	public $RicardoVerify;
	
	/**
	 * @var string
	 */
	public $RicardoVerifyErr;
	
	/**
	 * @var int
	 */
	public $RicardoWarranty;
	
	/**
	 * @var string
	 */
	public $SKU;
	
	/**
	 * @var int
	 */
	public $SaveDir;
	
	/**
	 * @var int
	 */
	public $SellerPays;
	
	/**
	 * @var string
	 */
	public $ShipToLocations;
	
	/**
	 * @var int
	 */
	public $ShippingDiscountProfileID;
	
	/**
	 * @var string
	 */
	public $ShippingLocations1;
	
	/**
	 * @var string
	 */
	public $ShippingLocations2;
	
	/**
	 * @var string
	 */
	public $ShippingLocations3;
	
	/**
	 * @var int
	 */
	public $ShiptermsSeeDescription;
	
	/**
	 * @var string
	 */
	public $StoreCategory;
	
	/**
	 * @var string
	 */
	public $StoreCategory2;
	
	/**
	 * @var string
	 */
	public $Subtitletext;
	
	/**
	 * @var string
	 */
	public $Title;
	
	/**
	 * @var string
	 */
	public $TransmitMPR;
	
	/**
	 * @var int
	 */
	public $Type;
	
	/**
	 * @var int
	 */
	public $UseArticlePrice;
	
	/**
	 * @var float
	 */
	public $VAT;
}
