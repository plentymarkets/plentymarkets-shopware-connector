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


// Dependencies
require_once PY_SOAP . 'Models/PlentySoapResponseMessage.php';
require_once PY_SOAP . 'Models/PlentySoapResponseSubMessage.php';

/**
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsSoapClient extends SoapClient
{

	/**
	 *
	 * @var PlentymarketsSoapClient
	 */
	protected static $Instance;

	/**
	 *
	 * @param string $wsdl
	 * @param string $username
	 * @param string $userpass
	 * @return PlentymarketsSoapClient
	 */
	protected function __construct($wsdl, $username, $userpass, $dryrun = false)
	{
		// Options
		$options = array();
		$options['features'] = SOAP_SINGLE_ELEMENT_ARRAYS;
		$options['version'] = SOAP_1_2;
		if ($_SERVER['SERVER_ADDR'] != '127.0.0.1')
		{
			$options['cache_wsdl'] = WSDL_CACHE_NONE;
		}
		$options['exceptions'] = true;
		$options['trace'] = true;

		//
		parent::__construct($wsdl, $options);

		// Check whether auth cache exist and whether the file is from today
		if (!$dryrun && date('Y-m-d', PlentymarketsConfig::getInstance()->getApiLastAuthTimestamp(0)) == date('Y-m-d'))
		{
			$userID = PlentymarketsConfig::getInstance()->getApiUserID(-1);
			$token = PlentymarketsConfig::getInstance()->getApiToken('unknown');
		}
		else
		{
			// Load the request model
			require_once PY_SOAP . 'Models/PlentySoapRequest/GetAuthentificationToken.php';

			// Authentication
			$Request_GetAuthentificationToken = new PlentySoapRequest_GetAuthentificationToken();
			$Request_GetAuthentificationToken->Username = $username;
			$Request_GetAuthentificationToken->Userpass = $userpass;

			$Response_GetAuthentificationToken = $this->GetAuthentificationToken($Request_GetAuthentificationToken);

			if ($Response_GetAuthentificationToken->Success == true)
			{
				$userID = $Response_GetAuthentificationToken->UserID;
				$token = $Response_GetAuthentificationToken->Token;

				if (!$dryrun)
				{
					PlentymarketsConfig::getInstance()->setApiUserID($userID);
					PlentymarketsConfig::getInstance()->setApiToken($token);
					PlentymarketsConfig::getInstance()->setApiLastAuthTimestamp(time());

					// Log
					PlentymarketsLogger::getInstance()->message('Soap:Auth', 'Received a new token');
				}
				else
				{
					// Log
					PlentymarketsLogger::getInstance()->message('Soap:Auth', 'API credentials tested successully');
				}
			}
			else
			{
				if (!$dryrun)
				{
					PlentymarketsLogger::getInstance()->message('Soap:Auth', 'Invalid API credentials');
				}
				else
				{
					PlentymarketsLogger::getInstance()->message('Soap:Auth', 'Invalid API credentials');
				}

				throw new Exception('Credentials invalid');

			}
		}

		//
		$authentication = array(
			'UserID' => $userID,
			'Token' => $token
		);

		//
		$this->__setSoapHeaders(new SoapHeader($wsdl, 'verifyingToken', new SoapVar($authentication, SOAP_ENC_OBJECT)));
	}

	public function __call($call, $args)
	{
		$method = '_' . $call;
		try
		{
			if (count($args))
			{
				$Response = $this->$method($args[0]);
			}
			else
			{
				$Response = $this->$method();
			}
		}
		catch (Exception $E)
		{
		}

		if ($Response->Success == true)
		{
			PlentymarketsLogger::getInstance()->callMessage($call, $this->__getLastRequest(), $this->__getLastResponse());
		}
		else
		{
			PlentymarketsLogger::getInstance()->callError($call, $this->__getLastRequest(), $this->__getLastResponse());
		}

		return $Response;
	}

	/**
	 *
	 * @param string $wsdl
	 * @param string $username
	 * @param string $userpass
	 * @return PlentymarketsSoapClient
	 */
	public static function getInstance()
	{
		if (!self::$Instance instanceof self)
		{
			$PlentymarketsConfig = PlentymarketsConfig::getInstance();
			self::$Instance = new self($PlentymarketsConfig->getApiWsdl() . '/plenty/api/soap/version110/?xml', $PlentymarketsConfig->getApiUsername(), $PlentymarketsConfig->getApiPassword());
		}

		return self::$Instance;
	}

	/**
	 *
	 * @param string $wsdl
	 * @param string $username
	 * @param string $userpass
	 * @return PlentymarketsSoapClient
	 */
	public static function getTestInstance($wsdl, $username, $password)
	{
		return new self($wsdl, $username, $password, true);
	}

	/**
	 *
	 * @var PlentySoapRequest_AddAuction $Request_AddAuction
	 * @return PlentySoapResponse_AddAuction
	 */
	public function _AddAuction(PlentySoapRequest_AddAuction $Request_AddAuction)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/AddAuction.php';
		return parent::__soapCall('AddAuction', array(
			$Request_AddAuction
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_AddClientOrder $Request_AddClientOrder
	 * @return PlentySoapResponse_AddClientOrder
	 */
	public function _AddClientOrder(PlentySoapRequest_AddClientOrder $Request_AddClientOrder)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/AddClientOrder.php';
		return parent::__soapCall('AddClientOrder', array(
			$Request_AddClientOrder
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_AddCustomerDeliveryAddresses $Request_AddCustomerDeliveryAddresses
	 * @return PlentySoapResponse_AddCustomerDeliveryAddresses
	 */
	public function _AddCustomerDeliveryAddresses(PlentySoapRequest_AddCustomerDeliveryAddresses $Request_AddCustomerDeliveryAddresses)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/AddCustomerDeliveryAddresses.php';
		return parent::__soapCall('AddCustomerDeliveryAddresses', array(
			$Request_AddCustomerDeliveryAddresses
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_AddCustomerNote $Request_AddCustomerNote
	 * @return PlentySoapResponse_AddCustomerNote
	 */
	public function _AddCustomerNote(PlentySoapRequest_AddCustomerNote $Request_AddCustomerNote)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/AddCustomerNote.php';
		return parent::__soapCall('AddCustomerNote', array(
			$Request_AddCustomerNote
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_AddCustomers $Request_AddCustomers
	 * @return PlentySoapResponse_AddCustomers
	 */
	public function _AddCustomers(PlentySoapRequest_AddCustomers $Request_AddCustomers)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/AddCustomers.php';
		return parent::__soapCall('AddCustomers', array(
			$Request_AddCustomers
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_AddDeliveryOrder $Request_AddDeliveryOrder
	 * @return PlentySoapResponse_AddDeliveryOrder
	 */
	public function _AddDeliveryOrder(PlentySoapRequest_AddDeliveryOrder $Request_AddDeliveryOrder)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/AddDeliveryOrder.php';
		return parent::__soapCall('AddDeliveryOrder', array(
			$Request_AddDeliveryOrder
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_AddEmailTemplates $Request_AddEmailTemplates
	 * @return PlentySoapResponse_AddEmailTemplates
	 */
	public function _AddEmailTemplates(PlentySoapRequest_AddEmailTemplates $Request_AddEmailTemplates)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/AddEmailTemplates.php';
		return parent::__soapCall('AddEmailTemplates', array(
			$Request_AddEmailTemplates
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_AddIncomingPayments $Request_AddIncomingPayments
	 * @return PlentySoapResponse_AddIncomingPayments
	 */
	public function _AddIncomingPayments(PlentySoapRequest_AddIncomingPayments $Request_AddIncomingPayments)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/AddIncomingPayments.php';
		return parent::__soapCall('AddIncomingPayments', array(
			$Request_AddIncomingPayments
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_AddItemAttribute $Request_AddItemAttribute
	 * @return PlentySoapResponse_AddItemAttribute
	 */
	public function _AddItemAttribute(PlentySoapRequest_AddItemAttribute $Request_AddItemAttribute)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/AddItemAttribute.php';
		return parent::__soapCall('AddItemAttribute', array(
			$Request_AddItemAttribute
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_AddItemAttributeValueSets $Request_AddItemAttributeValueSets
	 * @return PlentySoapResponse_AddItemAttributeValueSets
	 */
	public function _AddItemAttributeValueSets(PlentySoapRequest_AddItemAttributeValueSets $Request_AddItemAttributeValueSets)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/AddItemAttributeValueSets.php';
		return parent::__soapCall('AddItemAttributeValueSets', array(
			$Request_AddItemAttributeValueSets
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_AddItemCategory $Request_AddItemCategory
	 * @return PlentySoapResponse_AddItemCategory
	 */
	public function _AddItemCategory(PlentySoapRequest_AddItemCategory $Request_AddItemCategory)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/AddItemCategory.php';
		return parent::__soapCall('AddItemCategory', array(
			$Request_AddItemCategory
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_AddItemMediaFile $Request_AddItemMediaFile
	 * @return PlentySoapResponse_AddItemMediaFile
	 */
	public function _AddItemMediaFile(PlentySoapRequest_AddItemMediaFile $Request_AddItemMediaFile)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/AddItemMediaFile.php';
		return parent::__soapCall('AddItemMediaFile', array(
			$Request_AddItemMediaFile
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_AddItemsBase $Request_AddItemsBase
	 * @return PlentySoapResponse_AddItemsBase
	 */
	public function _AddItemsBase(PlentySoapRequest_AddItemsBase $Request_AddItemsBase)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/AddItemsBase.php';
		return parent::__soapCall('AddItemsBase', array(
			$Request_AddItemsBase
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_AddItemsImage $Request_AddItemsImage
	 * @return PlentySoapResponse_AddItemsImage
	 */
	public function _AddItemsImage(PlentySoapRequest_AddItemsImage $Request_AddItemsImage)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/AddItemsImage.php';
		return parent::__soapCall('AddItemsImage', array(
			$Request_AddItemsImage
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_AddItemsToBundle $Request_AddItemsToBundle
	 * @return PlentySoapResponse_AddItemsToBundle
	 */
	public function _AddItemsToBundle(PlentySoapRequest_AddItemsToBundle $Request_AddItemsToBundle)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/AddItemsToBundle.php';
		return parent::__soapCall('AddItemsToBundle', array(
			$Request_AddItemsToBundle
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_AddLinkedItems $Request_AddLinkedItems
	 * @return PlentySoapResponse_AddLinkedItems
	 */
	public function _AddLinkedItems(PlentySoapRequest_AddLinkedItems $Request_AddLinkedItems)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/AddLinkedItems.php';
		return parent::__soapCall('AddLinkedItems', array(
			$Request_AddLinkedItems
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_AddOrderItems $Request_AddOrderItems
	 * @return PlentySoapResponse_AddOrderItems
	 */
	public function _AddOrderItems(PlentySoapRequest_AddOrderItems $Request_AddOrderItems)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/AddOrderItems.php';
		return parent::__soapCall('AddOrderItems', array(
			$Request_AddOrderItems
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_AddOrderStatusHistory $Request_AddOrderStatusHistory
	 * @return PlentySoapResponse_AddOrderStatusHistory
	 */
	public function _AddOrderStatusHistory(PlentySoapRequest_AddOrderStatusHistory $Request_AddOrderStatusHistory)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/AddOrderStatusHistory.php';
		return parent::__soapCall('AddOrderStatusHistory', array(
			$Request_AddOrderStatusHistory
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_AddOrders $Request_AddOrders
	 * @return PlentySoapResponse_AddOrders
	 */
	public function _AddOrders(PlentySoapRequest_AddOrders $Request_AddOrders)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/AddOrders.php';
		return parent::__soapCall('AddOrders', array(
			$Request_AddOrders
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_AddOrdersInvoice $Request_AddOrdersInvoice
	 * @return PlentySoapResponse_AddOrdersInvoice
	 */
	public function _AddOrdersInvoice(PlentySoapRequest_AddOrdersInvoice $Request_AddOrdersInvoice)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/AddOrdersInvoice.php';
		return parent::__soapCall('AddOrdersInvoice', array(
			$Request_AddOrdersInvoice
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_AddOrdersPackageNumber $Request_AddOrdersPackageNumber
	 * @return PlentySoapResponse_AddOrdersPackageNumber
	 */
	public function _AddOrdersPackageNumber(PlentySoapRequest_AddOrdersPackageNumber $Request_AddOrdersPackageNumber)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/AddOrdersPackageNumber.php';
		return parent::__soapCall('AddOrdersPackageNumber', array(
			$Request_AddOrdersPackageNumber
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_AddProperty $Request_AddProperty
	 * @return PlentySoapResponse_AddProperty
	 */
	public function _AddProperty(PlentySoapRequest_AddProperty $Request_AddProperty)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/AddProperty.php';
		return parent::__soapCall('AddProperty', array(
			$Request_AddProperty
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_AddPropertyGroup $Request_AddPropertyGroup
	 * @return PlentySoapResponse_AddPropertyGroup
	 */
	public function _AddPropertyGroup(PlentySoapRequest_AddPropertyGroup $Request_AddPropertyGroup)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/AddPropertyGroup.php';
		return parent::__soapCall('AddPropertyGroup', array(
			$Request_AddPropertyGroup
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_AddPropertyToItem $Request_AddPropertyToItem
	 * @return PlentySoapResponse_AddPropertyToItem
	 */
	public function _AddPropertyToItem(PlentySoapRequest_AddPropertyToItem $Request_AddPropertyToItem)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/AddPropertyToItem.php';
		return parent::__soapCall('AddPropertyToItem', array(
			$Request_AddPropertyToItem
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_AddReorder $Request_AddReorder
	 * @return PlentySoapResponse_AddReorder
	 */
	public function _AddReorder(PlentySoapRequest_AddReorder $Request_AddReorder)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/AddReorder.php';
		return parent::__soapCall('AddReorder', array(
			$Request_AddReorder
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_AddShippingProfile $Request_AddShippingProfile
	 * @return PlentySoapResponse_AddShippingProfile
	 */
	public function _AddShippingProfile(PlentySoapRequest_AddShippingProfile $Request_AddShippingProfile)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/AddShippingProfile.php';
		return parent::__soapCall('AddShippingProfile', array(
			$Request_AddShippingProfile
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_AddTicket $Request_AddTicket
	 * @return PlentySoapResponse_AddTicket
	 */
	public function _AddTicket(PlentySoapRequest_AddTicket $Request_AddTicket)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/AddTicket.php';
		return parent::__soapCall('AddTicket', array(
			$Request_AddTicket
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_AddTicketLeafe $Request_AddTicketLeafe
	 * @return PlentySoapResponse_AddTicketLeafe
	 */
	public function _AddTicketLeafe(PlentySoapRequest_AddTicketLeafe $Request_AddTicketLeafe)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/AddTicketLeafe.php';
		return parent::__soapCall('AddTicketLeafe', array(
			$Request_AddTicketLeafe
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_DeleteEmailTemplates $Request_DeleteEmailTemplates
	 * @return PlentySoapResponse_DeleteEmailTemplates
	 */
	public function _DeleteEmailTemplates(PlentySoapRequest_DeleteEmailTemplates $Request_DeleteEmailTemplates)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/DeleteEmailTemplates.php';
		return parent::__soapCall('DeleteEmailTemplates', array(
			$Request_DeleteEmailTemplates
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_DeleteItemAttribute $Request_DeleteItemAttribute
	 * @return PlentySoapResponse_DeleteItemAttribute
	 */
	public function _DeleteItemAttribute(PlentySoapRequest_DeleteItemAttribute $Request_DeleteItemAttribute)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/DeleteItemAttribute.php';
		return parent::__soapCall('DeleteItemAttribute', array(
			$Request_DeleteItemAttribute
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_DeleteItemMediaFile $Request_DeleteItemMediaFile
	 * @return PlentySoapResponse_DeleteItemMediaFile
	 */
	public function _DeleteItemMediaFile(PlentySoapRequest_DeleteItemMediaFile $Request_DeleteItemMediaFile)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/DeleteItemMediaFile.php';
		return parent::__soapCall('DeleteItemMediaFile', array(
			$Request_DeleteItemMediaFile
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_DeleteItems $Request_DeleteItems
	 * @return PlentySoapResponse_DeleteItems
	 */
	public function _DeleteItems(PlentySoapRequest_DeleteItems $Request_DeleteItems)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/DeleteItems.php';
		return parent::__soapCall('DeleteItems', array(
			$Request_DeleteItems
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_DeleteItemsImages $Request_DeleteItemsImages
	 * @return PlentySoapResponse_DeleteItemsImages
	 */
	public function _DeleteItemsImages(PlentySoapRequest_DeleteItemsImages $Request_DeleteItemsImages)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/DeleteItemsImages.php';
		return parent::__soapCall('DeleteItemsImages', array(
			$Request_DeleteItemsImages
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_DeleteItemsSuppliers $Request_DeleteItemsSuppliers
	 * @return PlentySoapResponse_DeleteItemsSuppliers
	 */
	public function _DeleteItemsSuppliers(PlentySoapRequest_DeleteItemsSuppliers $Request_DeleteItemsSuppliers)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/DeleteItemsSuppliers.php';
		return parent::__soapCall('DeleteItemsSuppliers', array(
			$Request_DeleteItemsSuppliers
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_DeleteLinkedItems $Request_DeleteLinkedItems
	 * @return PlentySoapResponse_DeleteLinkedItems
	 */
	public function _DeleteLinkedItems(PlentySoapRequest_DeleteLinkedItems $Request_DeleteLinkedItems)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/DeleteLinkedItems.php';
		return parent::__soapCall('DeleteLinkedItems', array(
			$Request_DeleteLinkedItems
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_DeleteOrderPackageNumber $Request_DeleteOrderPackageNumber
	 * @return PlentySoapResponse_DeleteOrderPackageNumber
	 */
	public function _DeleteOrderPackageNumber(PlentySoapRequest_DeleteOrderPackageNumber $Request_DeleteOrderPackageNumber)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/DeleteOrderPackageNumber.php';
		return parent::__soapCall('DeleteOrderPackageNumber', array(
			$Request_DeleteOrderPackageNumber
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_DeletePriceSets $Request_DeletePriceSets
	 * @return PlentySoapResponse_DeletePriceSets
	 */
	public function _DeletePriceSets(PlentySoapRequest_DeletePriceSets $Request_DeletePriceSets)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/DeletePriceSets.php';
		return parent::__soapCall('DeletePriceSets', array(
			$Request_DeletePriceSets
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_DeleteProperty $Request_DeleteProperty
	 * @return PlentySoapResponse_DeleteProperty
	 */
	public function _DeleteProperty(PlentySoapRequest_DeleteProperty $Request_DeleteProperty)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/DeleteProperty.php';
		return parent::__soapCall('DeleteProperty', array(
			$Request_DeleteProperty
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_DeletePropertyGroup $Request_DeletePropertyGroup
	 * @return PlentySoapResponse_DeletePropertyGroup
	 */
	public function _DeletePropertyGroup(PlentySoapRequest_DeletePropertyGroup $Request_DeletePropertyGroup)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/DeletePropertyGroup.php';
		return parent::__soapCall('DeletePropertyGroup', array(
			$Request_DeletePropertyGroup
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_DeleteReorder $Request_DeleteReorder
	 * @return PlentySoapResponse_DeleteReorder
	 */
	public function _DeleteReorder(PlentySoapRequest_DeleteReorder $Request_DeleteReorder)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/DeleteReorder.php';
		return parent::__soapCall('DeleteReorder', array(
			$Request_DeleteReorder
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_GetAttributeValueSets $Request_GetAttributeValueSets
	 * @return PlentySoapResponse_GetAttributeValueSets
	 */
	public function _GetAttributeValueSets(PlentySoapRequest_GetAttributeValueSets $Request_GetAttributeValueSets)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetAttributeValueSets.php';
		return parent::__soapCall('GetAttributeValueSets', array(
			$Request_GetAttributeValueSets
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_GetAuctionMarketsShopCategories $Request_GetAuctionMarketsShopCategories
	 * @return PlentySoapResponse_GetAuctionMarketsShopCategories
	 */
	public function _GetAuctionMarketsShopCategories(PlentySoapRequest_GetAuctionMarketsShopCategories $Request_GetAuctionMarketsShopCategories)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetAuctionMarketsShopCategories.php';
		return parent::__soapCall('GetAuctionMarketsShopCategories', array(
			$Request_GetAuctionMarketsShopCategories
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_GetAuctions $Request_GetAuctions
	 * @return PlentySoapResponse_GetAuctions
	 */
	public function _GetAuctions(PlentySoapRequest_GetAuctions $Request_GetAuctions)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetAuctions.php';
		return parent::__soapCall('GetAuctions', array(
			$Request_GetAuctions
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_GetAuthentificationToken $Request_GetAuthentificationToken
	 * @return PlentySoapResponse_GetAuthentificationToken
	 */
	public function _GetAuthentificationToken(PlentySoapRequest_GetAuthentificationToken $Request_GetAuthentificationToken)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetAuthentificationToken.php';
		return parent::__soapCall('GetAuthentificationToken', array(
			$Request_GetAuthentificationToken
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_GetCategoryMappingForMarket $Request_GetCategoryMappingForMarket
	 * @return PlentySoapResponse_GetCategoryMappingForMarket
	 */
	public function _GetCategoryMappingForMarket(PlentySoapRequest_GetCategoryMappingForMarket $Request_GetCategoryMappingForMarket)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetCategoryMappingForMarket.php';
		return parent::__soapCall('GetCategoryMappingForMarket', array(
			$Request_GetCategoryMappingForMarket
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_GetContentPage $Request_GetContentPage
	 * @return PlentySoapResponse_GetContentPage
	 */
	public function _GetContentPage(PlentySoapRequest_GetContentPage $Request_GetContentPage)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetContentPage.php';
		return parent::__soapCall('GetContentPage', array(
			$Request_GetContentPage
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_GetCountriesOfDelivery $Request_GetCountriesOfDelivery
	 * @return PlentySoapResponse_GetCountriesOfDelivery
	 */
	public function _GetCountriesOfDelivery(PlentySoapRequest_GetCountriesOfDelivery $Request_GetCountriesOfDelivery)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetCountriesOfDelivery.php';
		return parent::__soapCall('GetCountriesOfDelivery', array(
			$Request_GetCountriesOfDelivery
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_GetCurrentStocks $Request_GetCurrentStocks
	 * @return PlentySoapResponse_GetCurrentStocks
	 */
	public function _GetCurrentStocks(PlentySoapRequest_GetCurrentStocks $Request_GetCurrentStocks)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetCurrentStocks.php';
		return parent::__soapCall('GetCurrentStocks', array(
			$Request_GetCurrentStocks
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_GetCurrentStocks4Marketplace $Request_GetCurrentStocks4Marketplace
	 * @return PlentySoapResponse_GetCurrentStocks4Marketplace
	 */
	public function _GetCurrentStocks4Marketplace(PlentySoapRequest_GetCurrentStocks4Marketplace $Request_GetCurrentStocks4Marketplace)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetCurrentStocks4Marketplace.php';
		return parent::__soapCall('GetCurrentStocks4Marketplace', array(
			$Request_GetCurrentStocks4Marketplace
		));
	}

	/**
	 *
	 * @return PlentySoapResponse_GetCustomerClasses
	 */
	public function _GetCustomerClasses()
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetCustomerClasses.php';
		return parent::__soapCall('GetCustomerClasses', array());
	}

	/**
	 *
	 * @var PlentySoapRequest_GetCustomerDeliveryAddresses $Request_GetCustomerDeliveryAddresses
	 * @return PlentySoapResponse_GetCustomerDeliveryAddresses
	 */
	public function _GetCustomerDeliveryAddresses(PlentySoapRequest_GetCustomerDeliveryAddresses $Request_GetCustomerDeliveryAddresses)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetCustomerDeliveryAddresses.php';
		return parent::__soapCall('GetCustomerDeliveryAddresses', array(
			$Request_GetCustomerDeliveryAddresses
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_GetCustomerNotes $Request_GetCustomerNotes
	 * @return PlentySoapResponse_GetCustomerNotes
	 */
	public function _GetCustomerNotes(PlentySoapRequest_GetCustomerNotes $Request_GetCustomerNotes)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetCustomerNotes.php';
		return parent::__soapCall('GetCustomerNotes', array(
			$Request_GetCustomerNotes
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_GetCustomerOrderOverviewLink $Request_GetCustomerOrderOverviewLink
	 * @return PlentySoapResponse_GetCustomerOrderOverviewLink
	 */
	public function _GetCustomerOrderOverviewLink(PlentySoapRequest_GetCustomerOrderOverviewLink $Request_GetCustomerOrderOverviewLink)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetCustomerOrderOverviewLink.php';
		return parent::__soapCall('GetCustomerOrderOverviewLink', array(
			$Request_GetCustomerOrderOverviewLink
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_GetCustomerOrders $Request_GetCustomerOrders
	 * @return PlentySoapResponse_GetCustomerOrders
	 */
	public function _GetCustomerOrders(PlentySoapRequest_GetCustomerOrders $Request_GetCustomerOrders)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetCustomerOrders.php';
		return parent::__soapCall('GetCustomerOrders', array(
			$Request_GetCustomerOrders
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_GetCustomers $Request_GetCustomers
	 * @return PlentySoapResponse_GetCustomers
	 */
	public function _GetCustomers(PlentySoapRequest_GetCustomers $Request_GetCustomers)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetCustomers.php';
		return parent::__soapCall('GetCustomers', array(
			$Request_GetCustomers
		));
	}

	/**
	 *
	 * @return PlentySoapResponse_GetDefaultAttributeTypeForMarket
	 */
	public function _GetDefaultAttributeTypeForMarket()
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetDefaultAttributeTypeForMarket.php';
		return parent::__soapCall('GetDefaultAttributeTypeForMarket', array());
	}

	/**
	 *
	 * @var PlentySoapRequest_GetDynamicExport $Request_GetDynamicExport
	 * @return PlentySoapResponse_GetDynamicExport
	 */
	public function _GetDynamicExport(PlentySoapRequest_GetDynamicExport $Request_GetDynamicExport)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetDynamicExport.php';
		return parent::__soapCall('GetDynamicExport', array(
			$Request_GetDynamicExport
		));
	}

	/**
	 *
	 * @return PlentySoapResponse_GetDynamicFormats
	 */
	public function _GetDynamicFormats()
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetDynamicFormats.php';
		return parent::__soapCall('GetDynamicFormats', array());
	}

	/**
	 *
	 * @return PlentySoapResponse_GetDynamicImportStack
	 */
	public function _GetDynamicImportStack()
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetDynamicImportStack.php';
		return parent::__soapCall('GetDynamicImportStack', array());
	}

	/**
	 *
	 * @var PlentySoapRequest_GetEbayItemVariations $Request_GetEbayItemVariations
	 * @return PlentySoapResponse_GetEbayItemVariations
	 */
	public function _GetEbayItemVariations(PlentySoapRequest_GetEbayItemVariations $Request_GetEbayItemVariations)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetEbayItemVariations.php';
		return parent::__soapCall('GetEbayItemVariations', array(
			$Request_GetEbayItemVariations
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_GetEmailTemplate $Request_GetEmailTemplate
	 * @return PlentySoapResponse_GetEmailTemplate
	 */
	public function _GetEmailTemplate(PlentySoapRequest_GetEmailTemplate $Request_GetEmailTemplate)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetEmailTemplate.php';
		return parent::__soapCall('GetEmailTemplate', array(
			$Request_GetEmailTemplate
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_GetEmailTemplates $Request_GetEmailTemplates
	 * @return PlentySoapResponse_GetEmailTemplates
	 */
	public function _GetEmailTemplates(PlentySoapRequest_GetEmailTemplates $Request_GetEmailTemplates)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetEmailTemplates.php';
		return parent::__soapCall('GetEmailTemplates', array(
			$Request_GetEmailTemplates
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_GetIncomingPayments $Request_GetIncomingPayments
	 * @return PlentySoapResponse_GetIncomingPayments
	 */
	public function _GetIncomingPayments(PlentySoapRequest_GetIncomingPayments $Request_GetIncomingPayments)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetIncomingPayments.php';
		return parent::__soapCall('GetIncomingPayments', array(
			$Request_GetIncomingPayments
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_GetItemAttributes $Request_GetItemAttributes
	 * @return PlentySoapResponse_GetItemAttributes
	 */
	public function _GetItemAttributes(PlentySoapRequest_GetItemAttributes $Request_GetItemAttributes)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetItemAttributes.php';
		return parent::__soapCall('GetItemAttributes', array(
			$Request_GetItemAttributes
		));
	}

	/**
	 *
	 * @return PlentySoapResponse_GetItemAvailabilityConfig
	 */
	public function _GetItemAvailabilityConfig()
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetItemAvailabilityConfig.php';
		return parent::__soapCall('GetItemAvailabilityConfig', array());
	}

	/**
	 *
	 * @var PlentySoapRequest_GetItemBundles $Request_GetItemBundles
	 * @return PlentySoapResponse_GetItemBundles
	 */
	public function _GetItemBundles(PlentySoapRequest_GetItemBundles $Request_GetItemBundles)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetItemBundles.php';
		return parent::__soapCall('GetItemBundles', array(
			$Request_GetItemBundles
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_GetItemCategoryCatalog $Request_GetItemCategoryCatalog
	 * @return PlentySoapResponse_GetItemCategoryCatalog
	 */
	public function _GetItemCategoryCatalog(PlentySoapRequest_GetItemCategoryCatalog $Request_GetItemCategoryCatalog)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetItemCategoryCatalog.php';
		return parent::__soapCall('GetItemCategoryCatalog', array(
			$Request_GetItemCategoryCatalog
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_GetItemCategoryCatalogBase $Request_GetItemCategoryCatalogBase
	 * @return PlentySoapResponse_GetItemCategoryCatalogBase
	 */
	public function _GetItemCategoryCatalogBase(PlentySoapRequest_GetItemCategoryCatalogBase $Request_GetItemCategoryCatalogBase)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetItemCategoryCatalogBase.php';
		return parent::__soapCall('GetItemCategoryCatalogBase', array(
			$Request_GetItemCategoryCatalogBase
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_GetItemCategoryTree $Request_GetItemCategoryTree
	 * @return PlentySoapResponse_GetItemCategoryTree
	 */
	public function _GetItemCategoryTree(PlentySoapRequest_GetItemCategoryTree $Request_GetItemCategoryTree)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetItemCategoryTree.php';
		return parent::__soapCall('GetItemCategoryTree', array(
			$Request_GetItemCategoryTree
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_GetItemsBase $Request_GetItemsBase
	 * @return PlentySoapResponse_GetItemsBase
	 */
	public function _GetItemsBase(PlentySoapRequest_GetItemsBase $Request_GetItemsBase)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetItemsBase.php';
		return parent::__soapCall('GetItemsBase', array(
			$Request_GetItemsBase
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_GetItemsImages $Request_GetItemsImages
	 * @return PlentySoapResponse_GetItemsImages
	 */
	public function _GetItemsImages(PlentySoapRequest_GetItemsImages $Request_GetItemsImages)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetItemsImages.php';
		return parent::__soapCall('GetItemsImages', array(
			$Request_GetItemsImages
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_GetItemsPreviewLink $Request_GetItemsPreviewLink
	 * @return PlentySoapResponse_GetItemsPreviewLink
	 */
	public function _GetItemsPreviewLink(PlentySoapRequest_GetItemsPreviewLink $Request_GetItemsPreviewLink)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetItemsPreviewLink.php';
		return parent::__soapCall('GetItemsPreviewLink', array(
			$Request_GetItemsPreviewLink
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_GetItemsPriceLists $Request_GetItemsPriceLists
	 * @return PlentySoapResponse_GetItemsPriceLists
	 */
	public function _GetItemsPriceLists(PlentySoapRequest_GetItemsPriceLists $Request_GetItemsPriceLists)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetItemsPriceLists.php';
		return parent::__soapCall('GetItemsPriceLists', array(
			$Request_GetItemsPriceLists
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_GetItemsPriceUpdate $Request_GetItemsPriceUpdate
	 * @return PlentySoapResponse_GetItemsPriceUpdate
	 */
	public function _GetItemsPriceUpdate(PlentySoapRequest_GetItemsPriceUpdate $Request_GetItemsPriceUpdate)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetItemsPriceUpdate.php';
		return parent::__soapCall('GetItemsPriceUpdate', array(
			$Request_GetItemsPriceUpdate
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_GetItemsPropertiesList $Request_GetItemsPropertiesList
	 * @return PlentySoapResponse_GetItemsPropertiesList
	 */
	public function _GetItemsPropertiesList(PlentySoapRequest_GetItemsPropertiesList $Request_GetItemsPropertiesList)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetItemsPropertiesList.php';
		return parent::__soapCall('GetItemsPropertiesList', array(
			$Request_GetItemsPropertiesList
		));
	}

	/**
	 *
	 * @return PlentySoapResponse_GetItemsReturnStatus
	 */
	public function _GetItemsReturnStatus()
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetItemsReturnStatus.php';
		return parent::__soapCall('GetItemsReturnStatus', array());
	}

	/**
	 *
	 * @var PlentySoapRequest_GetItemsSearchData $Request_GetItemsSearchData
	 * @return PlentySoapResponse_GetItemsSearchData
	 */
	public function _GetItemsSearchData(PlentySoapRequest_GetItemsSearchData $Request_GetItemsSearchData)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetItemsSearchData.php';
		return parent::__soapCall('GetItemsSearchData', array(
			$Request_GetItemsSearchData
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_GetItemsStock $Request_GetItemsStock
	 * @return PlentySoapResponse_GetItemsStock
	 */
	public function _GetItemsStock(PlentySoapRequest_GetItemsStock $Request_GetItemsStock)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetItemsStock.php';
		return parent::__soapCall('GetItemsStock', array(
			$Request_GetItemsStock
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_GetItemsStockSearch $Request_GetItemsStockSearch
	 * @return PlentySoapResponse_GetItemsStockSearch
	 */
	public function _GetItemsStockSearch(PlentySoapRequest_GetItemsStockSearch $Request_GetItemsStockSearch)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetItemsStockSearch.php';
		return parent::__soapCall('GetItemsStockSearch', array(
			$Request_GetItemsStockSearch
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_GetItemsSuppliers $Request_GetItemsSuppliers
	 * @return PlentySoapResponse_GetItemsSuppliers
	 */
	public function _GetItemsSuppliers(PlentySoapRequest_GetItemsSuppliers $Request_GetItemsSuppliers)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetItemsSuppliers.php';
		return parent::__soapCall('GetItemsSuppliers', array(
			$Request_GetItemsSuppliers
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_GetItemsTexts $Request_GetItemsTexts
	 * @return PlentySoapResponse_GetItemsTexts
	 */
	public function _GetItemsTexts(PlentySoapRequest_GetItemsTexts $Request_GetItemsTexts)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetItemsTexts.php';
		return parent::__soapCall('GetItemsTexts', array(
			$Request_GetItemsTexts
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_GetItemsVariantImages $Request_GetItemsVariantImages
	 * @return PlentySoapResponse_GetItemsVariantImages
	 */
	public function _GetItemsVariantImages(PlentySoapRequest_GetItemsVariantImages $Request_GetItemsVariantImages)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetItemsVariantImages.php';
		return parent::__soapCall('GetItemsVariantImages', array(
			$Request_GetItemsVariantImages
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_GetItemsVariantsAvailable $Request_GetItemsVariantsAvailable
	 * @return PlentySoapResponse_GetItemsVariantsAvailable
	 */
	public function _GetItemsVariantsAvailable(PlentySoapRequest_GetItemsVariantsAvailable $Request_GetItemsVariantsAvailable)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetItemsVariantsAvailable.php';
		return parent::__soapCall('GetItemsVariantsAvailable', array(
			$Request_GetItemsVariantsAvailable
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_GetItemsWarehouseSettings $Request_GetItemsWarehouseSettings
	 * @return PlentySoapResponse_GetItemsWarehouseSettings
	 */
	public function _GetItemsWarehouseSettings(PlentySoapRequest_GetItemsWarehouseSettings $Request_GetItemsWarehouseSettings)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetItemsWarehouseSettings.php';
		return parent::__soapCall('GetItemsWarehouseSettings', array(
			$Request_GetItemsWarehouseSettings
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_GetLegalInformation $Request_GetLegalInformation
	 * @return PlentySoapResponse_GetLegalInformation
	 */
	public function _GetLegalInformation(PlentySoapRequest_GetLegalInformation $Request_GetLegalInformation)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetLegalInformation.php';
		return parent::__soapCall('GetLegalInformation', array(
			$Request_GetLegalInformation
		));
	}

	/**
	 *
	 * @return PlentySoapResponse_GetLinkPriceColumnToReferrer
	 */
	public function _GetLinkPriceColumnToReferrer()
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetLinkPriceColumnToReferrer.php';
		return parent::__soapCall('GetLinkPriceColumnToReferrer', array());
	}

	/**
	 *
	 * @var PlentySoapRequest_GetLinkedItems $Request_GetLinkedItems
	 * @return PlentySoapResponse_GetLinkedItems
	 */
	public function _GetLinkedItems(PlentySoapRequest_GetLinkedItems $Request_GetLinkedItems)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetLinkedItems.php';
		return parent::__soapCall('GetLinkedItems', array(
			$Request_GetLinkedItems
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_GetMarketItemNumbers $Request_GetMarketItemNumbers
	 * @return PlentySoapResponse_GetMarketItemNumbers
	 */
	public function _GetMarketItemNumbers(PlentySoapRequest_GetMarketItemNumbers $Request_GetMarketItemNumbers)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetMarketItemNumbers.php';
		return parent::__soapCall('GetMarketItemNumbers', array(
			$Request_GetMarketItemNumbers
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_GetMarketplaceTransactions $Request_GetMarketplaceTransactions
	 * @return PlentySoapResponse_GetMarketplaceTransactions
	 */
	public function _GetMarketplaceTransactions(PlentySoapRequest_GetMarketplaceTransactions $Request_GetMarketplaceTransactions)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetMarketplaceTransactions.php';
		return parent::__soapCall('GetMarketplaceTransactions', array(
			$Request_GetMarketplaceTransactions
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_GetMeasureUnitConfig $Request_GetMeasureUnitConfig
	 * @return PlentySoapResponse_GetMeasureUnitConfig
	 */
	public function _GetMeasureUnitConfig(PlentySoapRequest_GetMeasureUnitConfig $Request_GetMeasureUnitConfig)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetMeasureUnitConfig.php';
		return parent::__soapCall('GetMeasureUnitConfig', array(
			$Request_GetMeasureUnitConfig
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_GetMethodOfPayments $Request_GetMethodOfPayments
	 * @return PlentySoapResponse_GetMethodOfPayments
	 */
	public function _GetMethodOfPayments(PlentySoapRequest_GetMethodOfPayments $Request_GetMethodOfPayments)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetMethodOfPayments.php';
		return parent::__soapCall('GetMethodOfPayments', array(
			$Request_GetMethodOfPayments
		));
	}

	/**
	 *
	 * @return PlentySoapResponse_GetMultiShops
	 */
	public function _GetMultiShops()
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetMultiShops.php';
		return parent::__soapCall('GetMultiShops', array());
	}

	/**
	 *
	 * @var PlentySoapRequest_GetOrderCheckout $Request_GetOrderCheckout
	 * @return PlentySoapResponse_GetOrderCheckout
	 */
	public function _GetOrderCheckout(PlentySoapRequest_GetOrderCheckout $Request_GetOrderCheckout)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetOrderCheckout.php';
		return parent::__soapCall('GetOrderCheckout', array(
			$Request_GetOrderCheckout
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_GetOrderStatusHistory $Request_GetOrderStatusHistory
	 * @return PlentySoapResponse_GetOrderStatusHistory
	 */
	public function _GetOrderStatusHistory(PlentySoapRequest_GetOrderStatusHistory $Request_GetOrderStatusHistory)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetOrderStatusHistory.php';
		return parent::__soapCall('GetOrderStatusHistory', array(
			$Request_GetOrderStatusHistory
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_GetOrderStatusList $Request_GetOrderStatusList
	 * @return PlentySoapResponse_GetOrderStatusList
	 */
	public function _GetOrderStatusList(PlentySoapRequest_GetOrderStatusList $Request_GetOrderStatusList)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetOrderStatusList.php';
		return parent::__soapCall('GetOrderStatusList', array(
			$Request_GetOrderStatusList
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_GetOrdersCreditNoteDocumentURLs $Request_GetOrdersCreditNoteDocumentURLs
	 * @return PlentySoapResponse_GetOrdersCreditNoteDocumentURLs
	 */
	public function _GetOrdersCreditNoteDocumentURLs(PlentySoapRequest_GetOrdersCreditNoteDocumentURLs $Request_GetOrdersCreditNoteDocumentURLs)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetOrdersCreditNoteDocumentURLs.php';
		return parent::__soapCall('GetOrdersCreditNoteDocumentURLs', array(
			$Request_GetOrdersCreditNoteDocumentURLs
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_GetOrdersDeliveryNoteDocumentURLs $Request_GetOrdersDeliveryNoteDocumentURLs
	 * @return PlentySoapResponse_GetOrdersDeliveryNoteDocumentURLs
	 */
	public function _GetOrdersDeliveryNoteDocumentURLs(PlentySoapRequest_GetOrdersDeliveryNoteDocumentURLs $Request_GetOrdersDeliveryNoteDocumentURLs)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetOrdersDeliveryNoteDocumentURLs.php';
		return parent::__soapCall('GetOrdersDeliveryNoteDocumentURLs', array(
			$Request_GetOrdersDeliveryNoteDocumentURLs
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_GetOrdersDunningLetterDocumentURLs $Request_GetOrdersDunningLetterDocumentURLs
	 * @return PlentySoapResponse_GetOrdersDunningLetterDocumentURLs
	 */
	public function _GetOrdersDunningLetterDocumentURLs(PlentySoapRequest_GetOrdersDunningLetterDocumentURLs $Request_GetOrdersDunningLetterDocumentURLs)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetOrdersDunningLetterDocumentURLs.php';
		return parent::__soapCall('GetOrdersDunningLetterDocumentURLs', array(
			$Request_GetOrdersDunningLetterDocumentURLs
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_GetOrdersInvoiceDocumentURLs $Request_GetOrdersInvoiceDocumentURLs
	 * @return PlentySoapResponse_GetOrdersInvoiceDocumentURLs
	 */
	public function _GetOrdersInvoiceDocumentURLs(PlentySoapRequest_GetOrdersInvoiceDocumentURLs $Request_GetOrdersInvoiceDocumentURLs)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetOrdersInvoiceDocumentURLs.php';
		return parent::__soapCall('GetOrdersInvoiceDocumentURLs', array(
			$Request_GetOrdersInvoiceDocumentURLs
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_GetOrdersOfferDocumentURLs $Request_GetOrdersOfferDocumentURLs
	 * @return PlentySoapResponse_GetOrdersOfferDocumentURLs
	 */
	public function _GetOrdersOfferDocumentURLs(PlentySoapRequest_GetOrdersOfferDocumentURLs $Request_GetOrdersOfferDocumentURLs)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetOrdersOfferDocumentURLs.php';
		return parent::__soapCall('GetOrdersOfferDocumentURLs', array(
			$Request_GetOrdersOfferDocumentURLs
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_GetOrdersOrderConfirmationDocumentURLs $Request_GetOrdersOrderConfirmationDocumentURLs
	 * @return PlentySoapResponse_GetOrdersOrderConfirmationDocumentURLs
	 */
	public function _GetOrdersOrderConfirmationDocumentURLs(PlentySoapRequest_GetOrdersOrderConfirmationDocumentURLs $Request_GetOrdersOrderConfirmationDocumentURLs)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetOrdersOrderConfirmationDocumentURLs.php';
		return parent::__soapCall('GetOrdersOrderConfirmationDocumentURLs', array(
			$Request_GetOrdersOrderConfirmationDocumentURLs
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_GetOrdersPaymentInformation $Request_GetOrdersPaymentInformation
	 * @return PlentySoapResponse_GetOrdersPaymentInformation
	 */
	public function _GetOrdersPaymentInformation(PlentySoapRequest_GetOrdersPaymentInformation $Request_GetOrdersPaymentInformation)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetOrdersPaymentInformation.php';
		return parent::__soapCall('GetOrdersPaymentInformation', array(
			$Request_GetOrdersPaymentInformation
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_GetOrdersShipmentNumbers $Request_GetOrdersShipmentNumbers
	 * @return PlentySoapResponse_GetOrdersShipmentNumbers
	 */
	public function _GetOrdersShipmentNumbers(PlentySoapRequest_GetOrdersShipmentNumbers $Request_GetOrdersShipmentNumbers)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetOrdersShipmentNumbers.php';
		return parent::__soapCall('GetOrdersShipmentNumbers', array(
			$Request_GetOrdersShipmentNumbers
		));
	}

	/**
	 *
	 * @return PlentySoapResponse_GetPlentyMarketsVersion
	 */
	public function _GetPlentyMarketsVersion()
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetPlentyMarketsVersion.php';
		return parent::__soapCall('GetPlentyMarketsVersion', array());
	}

	/**
	 *
	 * @return PlentySoapResponse_GetProducers
	 */
	public function _GetProducers()
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetProducers.php';
		return parent::__soapCall('GetProducers', array());
	}

	/**
	 *
	 * @var PlentySoapRequest_GetProperties $Request_GetProperties
	 * @return PlentySoapResponse_GetProperties
	 */
	public function _GetProperties(PlentySoapRequest_GetProperties $Request_GetProperties)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetProperties.php';
		return parent::__soapCall('GetProperties', array(
			$Request_GetProperties
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_GetPropertiesList $Request_GetPropertiesList
	 * @return PlentySoapResponse_GetPropertiesList
	 */
	public function _GetPropertiesList(PlentySoapRequest_GetPropertiesList $Request_GetPropertiesList)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetPropertiesList.php';
		return parent::__soapCall('GetPropertiesList', array(
			$Request_GetPropertiesList
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_GetPropertyGroups $Request_GetPropertyGroups
	 * @return PlentySoapResponse_GetPropertyGroups
	 */
	public function _GetPropertyGroups(PlentySoapRequest_GetPropertyGroups $Request_GetPropertyGroups)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetPropertyGroups.php';
		return parent::__soapCall('GetPropertyGroups', array(
			$Request_GetPropertyGroups
		));
	}

	/**
	 *
	 * @return PlentySoapResponse_GetReasonsForReturn
	 */
	public function _GetReasonsForReturn()
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetReasonsForReturn.php';
		return parent::__soapCall('GetReasonsForReturn', array());
	}

	/**
	 *
	 * @var PlentySoapRequest_GetReorder $Request_GetReorder
	 * @return PlentySoapResponse_GetReorder
	 */
	public function _GetReorder(PlentySoapRequest_GetReorder $Request_GetReorder)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetReorder.php';
		return parent::__soapCall('GetReorder', array(
			$Request_GetReorder
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_GetReturns $Request_GetReturns
	 * @return PlentySoapResponse_GetReturns
	 */
	public function _GetReturns(PlentySoapRequest_GetReturns $Request_GetReturns)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetReturns.php';
		return parent::__soapCall('GetReturns', array(
			$Request_GetReturns
		));
	}

	/**
	 *
	 * @return PlentySoapResponse_GetSalesOrderReferrer
	 */
	public function _GetSalesOrderReferrer()
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetSalesOrderReferrer.php';
		return parent::__soapCall('GetSalesOrderReferrer', array());
	}

	/**
	 *
	 * @return PlentySoapResponse_GetServerTime
	 */
	public function _GetServerTime()
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetServerTime.php';
		return parent::__soapCall('GetServerTime', array());
	}

	/**
	 *
	 * @var PlentySoapRequest_GetShippingProfiles $Request_GetShippingProfiles
	 * @return PlentySoapResponse_GetShippingProfiles
	 */
	public function _GetShippingProfiles(PlentySoapRequest_GetShippingProfiles $Request_GetShippingProfiles)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetShippingProfiles.php';
		return parent::__soapCall('GetShippingProfiles', array(
			$Request_GetShippingProfiles
		));
	}

	/**
	 *
	 * @return PlentySoapResponse_GetShippingServiceProvider
	 */
	public function _GetShippingServiceProvider()
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetShippingServiceProvider.php';
		return parent::__soapCall('GetShippingServiceProvider', array());
	}

	/**
	 *
	 * @var PlentySoapRequest_GetStockMovements $Request_GetStockMovements
	 * @return PlentySoapResponse_GetStockMovements
	 */
	public function _GetStockMovements(PlentySoapRequest_GetStockMovements $Request_GetStockMovements)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetStockMovements.php';
		return parent::__soapCall('GetStockMovements', array(
			$Request_GetStockMovements
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_GetTermsAndCancellation $Request_GetTermsAndCancellation
	 * @return PlentySoapResponse_GetTermsAndCancellation
	 */
	public function _GetTermsAndCancellation(PlentySoapRequest_GetTermsAndCancellation $Request_GetTermsAndCancellation)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetTermsAndCancellation.php';
		return parent::__soapCall('GetTermsAndCancellation', array(
			$Request_GetTermsAndCancellation
		));
	}

	/**
	 *
	 * @return PlentySoapResponse_GetVATConfig
	 */
	public function _GetVATConfig()
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetVATConfig.php';
		return parent::__soapCall('GetVATConfig', array());
	}

	/**
	 *
	 * @var PlentySoapRequest_GetWarehouseItem $Request_GetWarehouseItem
	 * @return PlentySoapResponse_GetWarehouseItem
	 */
	public function _GetWarehouseItem(PlentySoapRequest_GetWarehouseItem $Request_GetWarehouseItem)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetWarehouseItem.php';
		return parent::__soapCall('GetWarehouseItem', array(
			$Request_GetWarehouseItem
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_GetWarehouseList $Request_GetWarehouseList
	 * @return PlentySoapResponse_GetWarehouseList
	 */
	public function _GetWarehouseList(PlentySoapRequest_GetWarehouseList $Request_GetWarehouseList)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetWarehouseList.php';
		return parent::__soapCall('GetWarehouseList', array(
			$Request_GetWarehouseList
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_GetWarehouseStorageLocation $Request_GetWarehouseStorageLocation
	 * @return PlentySoapResponse_GetWarehouseStorageLocation
	 */
	public function _GetWarehouseStorageLocation(PlentySoapRequest_GetWarehouseStorageLocation $Request_GetWarehouseStorageLocation)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetWarehouseStorageLocation.php';
		return parent::__soapCall('GetWarehouseStorageLocation', array(
			$Request_GetWarehouseStorageLocation
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_GetWebshopSettings $Request_GetWebshopSettings
	 * @return PlentySoapResponse_GetWebshopSettings
	 */
	public function _GetWebshopSettings(PlentySoapRequest_GetWebshopSettings $Request_GetWebshopSettings)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/GetWebshopSettings.php';
		return parent::__soapCall('GetWebshopSettings', array(
			$Request_GetWebshopSettings
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_RemoveItemsFromBundle $Request_RemoveItemsFromBundle
	 * @return PlentySoapResponse_RemoveItemsFromBundle
	 */
	public function _RemoveItemsFromBundle(PlentySoapRequest_RemoveItemsFromBundle $Request_RemoveItemsFromBundle)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/RemoveItemsFromBundle.php';
		return parent::__soapCall('RemoveItemsFromBundle', array(
			$Request_RemoveItemsFromBundle
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_RemovePropertyFromItem $Request_RemovePropertyFromItem
	 * @return PlentySoapResponse_RemovePropertyFromItem
	 */
	public function _RemovePropertyFromItem(PlentySoapRequest_RemovePropertyFromItem $Request_RemovePropertyFromItem)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/RemovePropertyFromItem.php';
		return parent::__soapCall('RemovePropertyFromItem', array(
			$Request_RemovePropertyFromItem
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_SearchItemsSKU $Request_SearchItemsSKU
	 * @return PlentySoapResponse_SearchItemsSKU
	 */
	public function _SearchItemsSKU(PlentySoapRequest_SearchItemsSKU $Request_SearchItemsSKU)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/SearchItemsSKU.php';
		return parent::__soapCall('SearchItemsSKU', array(
			$Request_SearchItemsSKU
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_SearchOrders $Request_SearchOrders
	 * @return PlentySoapResponse_SearchOrders
	 */
	public function _SearchOrders(PlentySoapRequest_SearchOrders $Request_SearchOrders)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/SearchOrders.php';
		return parent::__soapCall('SearchOrders', array(
			$Request_SearchOrders
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_SetAttributeValueSetsDetails $Request_SetAttributeValueSetsDetails
	 * @return PlentySoapResponse_SetAttributeValueSetsDetails
	 */
	public function _SetAttributeValueSetsDetails(PlentySoapRequest_SetAttributeValueSetsDetails $Request_SetAttributeValueSetsDetails)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/SetAttributeValueSetsDetails.php';
		return parent::__soapCall('SetAttributeValueSetsDetails', array(
			$Request_SetAttributeValueSetsDetails
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_SetAuctionStartTimestamp $Request_SetAuctionStartTimestamp
	 * @return PlentySoapResponse_SetAuctionStartTimestamp
	 */
	public function _SetAuctionStartTimestamp(PlentySoapRequest_SetAuctionStartTimestamp $Request_SetAuctionStartTimestamp)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/SetAuctionStartTimestamp.php';
		return parent::__soapCall('SetAuctionStartTimestamp', array(
			$Request_SetAuctionStartTimestamp
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_SetBackPostOutgoingItems $Request_SetBackPostOutgoingItems
	 * @return PlentySoapResponse_SetBackPostOutgoingItems
	 */
	public function _SetBackPostOutgoingItems(PlentySoapRequest_SetBackPostOutgoingItems $Request_SetBackPostOutgoingItems)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/SetBackPostOutgoingItems.php';
		return parent::__soapCall('SetBackPostOutgoingItems', array(
			$Request_SetBackPostOutgoingItems
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_SetBankCreditCardData $Request_SetBankCreditCardData
	 * @return PlentySoapResponse_SetBankCreditCardData
	 */
	public function _SetBankCreditCardData(PlentySoapRequest_SetBankCreditCardData $Request_SetBankCreditCardData)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/SetBankCreditCardData.php';
		return parent::__soapCall('SetBankCreditCardData', array(
			$Request_SetBankCreditCardData
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_SetBookOutgoingItems $Request_SetBookOutgoingItems
	 * @return PlentySoapResponse_SetBookOutgoingItems
	 */
	public function _SetBookOutgoingItems(PlentySoapRequest_SetBookOutgoingItems $Request_SetBookOutgoingItems)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/SetBookOutgoingItems.php';
		return parent::__soapCall('SetBookOutgoingItems', array(
			$Request_SetBookOutgoingItems
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_SetContentPage $Request_SetContentPage
	 * @return PlentySoapResponse_SetContentPage
	 */
	public function _SetContentPage(PlentySoapRequest_SetContentPage $Request_SetContentPage)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/SetContentPage.php';
		return parent::__soapCall('SetContentPage', array(
			$Request_SetContentPage
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_SetCurrentStocks $Request_SetCurrentStocks
	 * @return PlentySoapResponse_SetCurrentStocks
	 */
	public function _SetCurrentStocks(PlentySoapRequest_SetCurrentStocks $Request_SetCurrentStocks)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/SetCurrentStocks.php';
		return parent::__soapCall('SetCurrentStocks', array(
			$Request_SetCurrentStocks
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_SetCustomerDeliveryAddresses $Request_SetCustomerDeliveryAddresses
	 * @return PlentySoapResponse_SetCustomerDeliveryAddresses
	 */
	public function _SetCustomerDeliveryAddresses(PlentySoapRequest_SetCustomerDeliveryAddresses $Request_SetCustomerDeliveryAddresses)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/SetCustomerDeliveryAddresses.php';
		return parent::__soapCall('SetCustomerDeliveryAddresses', array(
			$Request_SetCustomerDeliveryAddresses
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_SetCustomers $Request_SetCustomers
	 * @return PlentySoapResponse_SetCustomers
	 */
	public function _SetCustomers(PlentySoapRequest_SetCustomers $Request_SetCustomers)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/SetCustomers.php';
		return parent::__soapCall('SetCustomers', array(
			$Request_SetCustomers
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_SetDynamicImport $Request_SetDynamicImport
	 * @return PlentySoapResponse_SetDynamicImport
	 */
	public function _SetDynamicImport(PlentySoapRequest_SetDynamicImport $Request_SetDynamicImport)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/SetDynamicImport.php';
		return parent::__soapCall('SetDynamicImport', array(
			$Request_SetDynamicImport
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_SetEmailTemplates $Request_SetEmailTemplates
	 * @return PlentySoapResponse_SetEmailTemplates
	 */
	public function _SetEmailTemplates(PlentySoapRequest_SetEmailTemplates $Request_SetEmailTemplates)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/SetEmailTemplates.php';
		return parent::__soapCall('SetEmailTemplates', array(
			$Request_SetEmailTemplates
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_SetIncomingStocks $Request_SetIncomingStocks
	 * @return PlentySoapResponse_SetIncomingStocks
	 */
	public function _SetIncomingStocks(PlentySoapRequest_SetIncomingStocks $Request_SetIncomingStocks)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/SetIncomingStocks.php';
		return parent::__soapCall('SetIncomingStocks', array(
			$Request_SetIncomingStocks
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_SetItemsAvailability $Request_SetItemsAvailability
	 * @return PlentySoapResponse_SetItemsAvailability
	 */
	public function _SetItemsAvailability(PlentySoapRequest_SetItemsAvailability $Request_SetItemsAvailability)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/SetItemsAvailability.php';
		return parent::__soapCall('SetItemsAvailability', array(
			$Request_SetItemsAvailability
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_SetItemsBase $Request_SetItemsBase
	 * @return PlentySoapResponse_SetItemsBase
	 */
	public function _SetItemsBase(PlentySoapRequest_SetItemsBase $Request_SetItemsBase)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/SetItemsBase.php';
		return parent::__soapCall('SetItemsBase', array(
			$Request_SetItemsBase
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_SetItemsCategories $Request_SetItemsCategories
	 * @return PlentySoapResponse_SetItemsCategories
	 */
	public function _SetItemsCategories(PlentySoapRequest_SetItemsCategories $Request_SetItemsCategories)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/SetItemsCategories.php';
		return parent::__soapCall('SetItemsCategories', array(
			$Request_SetItemsCategories
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_SetItemsFreeTextFields $Request_SetItemsFreeTextFields
	 * @return PlentySoapResponse_SetItemsFreeTextFields
	 */
	public function _SetItemsFreeTextFields(PlentySoapRequest_SetItemsFreeTextFields $Request_SetItemsFreeTextFields)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/SetItemsFreeTextFields.php';
		return parent::__soapCall('SetItemsFreeTextFields', array(
			$Request_SetItemsFreeTextFields
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_SetItemsPurchasePrice $Request_SetItemsPurchasePrice
	 * @return PlentySoapResponse_SetItemsPurchasePrice
	 */
	public function _SetItemsPurchasePrice(PlentySoapRequest_SetItemsPurchasePrice $Request_SetItemsPurchasePrice)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/SetItemsPurchasePrice.php';
		return parent::__soapCall('SetItemsPurchasePrice', array(
			$Request_SetItemsPurchasePrice
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_SetItemsSuppliers $Request_SetItemsSuppliers
	 * @return PlentySoapResponse_SetItemsSuppliers
	 */
	public function _SetItemsSuppliers(PlentySoapRequest_SetItemsSuppliers $Request_SetItemsSuppliers)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/SetItemsSuppliers.php';
		return parent::__soapCall('SetItemsSuppliers', array(
			$Request_SetItemsSuppliers
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_SetItemsTexts $Request_SetItemsTexts
	 * @return PlentySoapResponse_SetItemsTexts
	 */
	public function _SetItemsTexts(PlentySoapRequest_SetItemsTexts $Request_SetItemsTexts)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/SetItemsTexts.php';
		return parent::__soapCall('SetItemsTexts', array(
			$Request_SetItemsTexts
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_SetItemsWarehouseSettings $Request_SetItemsWarehouseSettings
	 * @return PlentySoapResponse_SetItemsWarehouseSettings
	 */
	public function _SetItemsWarehouseSettings(PlentySoapRequest_SetItemsWarehouseSettings $Request_SetItemsWarehouseSettings)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/SetItemsWarehouseSettings.php';
		return parent::__soapCall('SetItemsWarehouseSettings', array(
			$Request_SetItemsWarehouseSettings
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_SetLegalInformation $Request_SetLegalInformation
	 * @return PlentySoapResponse_SetLegalInformation
	 */
	public function _SetLegalInformation(PlentySoapRequest_SetLegalInformation $Request_SetLegalInformation)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/SetLegalInformation.php';
		return parent::__soapCall('SetLegalInformation', array(
			$Request_SetLegalInformation
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_SetMarketItemNumbers $Request_SetMarketItemNumbers
	 * @return PlentySoapResponse_SetMarketItemNumbers
	 */
	public function _SetMarketItemNumbers(PlentySoapRequest_SetMarketItemNumbers $Request_SetMarketItemNumbers)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/SetMarketItemNumbers.php';
		return parent::__soapCall('SetMarketItemNumbers', array(
			$Request_SetMarketItemNumbers
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_SetOrderItems $Request_SetOrderItems
	 * @return PlentySoapResponse_SetOrderItems
	 */
	public function _SetOrderItems(PlentySoapRequest_SetOrderItems $Request_SetOrderItems)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/SetOrderItems.php';
		return parent::__soapCall('SetOrderItems', array(
			$Request_SetOrderItems
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_SetOrderItemsQuantity $Request_SetOrderItemsQuantity
	 * @return PlentySoapResponse_SetOrderItemsQuantity
	 */
	public function _SetOrderItemsQuantity(PlentySoapRequest_SetOrderItemsQuantity $Request_SetOrderItemsQuantity)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/SetOrderItemsQuantity.php';
		return parent::__soapCall('SetOrderItemsQuantity', array(
			$Request_SetOrderItemsQuantity
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_SetOrderStatus $Request_SetOrderStatus
	 * @return PlentySoapResponse_SetOrderStatus
	 */
	public function _SetOrderStatus(PlentySoapRequest_SetOrderStatus $Request_SetOrderStatus)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/SetOrderStatus.php';
		return parent::__soapCall('SetOrderStatus', array(
			$Request_SetOrderStatus
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_SetOrdersHead $Request_SetOrdersHead
	 * @return PlentySoapResponse_SetOrdersHead
	 */
	public function _SetOrdersHead(PlentySoapRequest_SetOrdersHead $Request_SetOrdersHead)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/SetOrdersHead.php';
		return parent::__soapCall('SetOrdersHead', array(
			$Request_SetOrdersHead
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_SetPriceSets $Request_SetPriceSets
	 * @return PlentySoapResponse_SetPriceSets
	 */
	public function _SetPriceSets(PlentySoapRequest_SetPriceSets $Request_SetPriceSets)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/SetPriceSets.php';
		return parent::__soapCall('SetPriceSets', array(
			$Request_SetPriceSets
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_SetProducers $Request_SetProducers
	 * @return PlentySoapResponse_SetProducers
	 */
	public function _SetProducers(PlentySoapRequest_SetProducers $Request_SetProducers)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/SetProducers.php';
		return parent::__soapCall('SetProducers', array(
			$Request_SetProducers
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_SetReturns $Request_SetReturns
	 * @return PlentySoapResponse_SetReturns
	 */
	public function _SetReturns(PlentySoapRequest_SetReturns $Request_SetReturns)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/SetReturns.php';
		return parent::__soapCall('SetReturns', array(
			$Request_SetReturns
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_SetShipmentRegistration $Request_SetShipmentRegistration
	 * @return PlentySoapResponse_SetShipmentRegistration
	 */
	public function _SetShipmentRegistration(PlentySoapRequest_SetShipmentRegistration $Request_SetShipmentRegistration)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/SetShipmentRegistration.php';
		return parent::__soapCall('SetShipmentRegistration', array(
			$Request_SetShipmentRegistration
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_SetStocksTransfer $Request_SetStocksTransfer
	 * @return PlentySoapResponse_SetStocksTransfer
	 */
	public function _SetStocksTransfer(PlentySoapRequest_SetStocksTransfer $Request_SetStocksTransfer)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/SetStocksTransfer.php';
		return parent::__soapCall('SetStocksTransfer', array(
			$Request_SetStocksTransfer
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_SetTermsAndCancellation $Request_SetTermsAndCancellation
	 * @return PlentySoapResponse_SetTermsAndCancellation
	 */
	public function _SetTermsAndCancellation(PlentySoapRequest_SetTermsAndCancellation $Request_SetTermsAndCancellation)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/SetTermsAndCancellation.php';
		return parent::__soapCall('SetTermsAndCancellation', array(
			$Request_SetTermsAndCancellation
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_SetWarehouse $Request_SetWarehouse
	 * @return PlentySoapResponse_SetWarehouse
	 */
	public function _SetWarehouse(PlentySoapRequest_SetWarehouse $Request_SetWarehouse)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/SetWarehouse.php';
		return parent::__soapCall('SetWarehouse', array(
			$Request_SetWarehouse
		));
	}

	/**
	 *
	 * @var PlentySoapRequest_SetWarranty $Request_SetWarranty
	 * @return PlentySoapResponse_SetWarranty
	 */
	public function _SetWarranty(PlentySoapRequest_SetWarranty $Request_SetWarranty)
	{
		require_once PY_SOAP . 'Models/PlentySoapResponse/SetWarranty.php';
		return parent::__soapCall('SetWarranty', array(
			$Request_SetWarranty
		));
	}
}
