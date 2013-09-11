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

require_once PY_SOAP . 'Models/PlentySoapObject/AddCustomersCustomer.php';
require_once PY_SOAP . 'Models/PlentySoapObject/CustomerBankData.php';
require_once PY_SOAP . 'Models/PlentySoapObject/CustomerFreeTestFields.php';
require_once PY_SOAP . 'Models/PlentySoapObject/AddCustomerDeliveryAddressesCustomer.php';
require_once PY_SOAP . 'Models/PlentySoapRequest/AddCustomers.php';
require_once PY_SOAP . 'Models/PlentySoapRequest/AddCustomerDeliveryAddresses.php';

/**
 * PlentymarketsExportEntityCustomer provides the actual customer export funcionality. Like the other export 
 * entities this class is called in PlentymarketsExportController. It is important to deliver the correct customer 
 * model to the constructor method of this class, which you can find at \Shopware\Models\Customer\Customer.
 * The data export takes place based on plentymarkets SOAP-calls.
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsExportEntityCustomer
{

	/**
	 *
	 * @var \Shopware\Models\Customer\Customer
	 */
	protected $Customer;
	protected $BillingAddress;
	protected $ShippingAddress;

	/**
	 *
	 * @var integer
	 */
	protected $PLENTY_customerID;

	/**
	 *
	 * @var array
	 */
	protected static $mappingFormOfAddress = array(
		'mr' => 0,
		'mrs' => 1,
		'company' => 2
	);

	/**
	 * Constructor method
	 *
	 * @param unknown $Customer
	 * @param string $BillingAddress
	 * @param string $ShippingAddress
	 */
	public function __construct($Customer, $BillingAddress=null, $ShippingAddress=null)
	{
		$this->Customer = $Customer;

		if ($BillingAddress === null)
		{
			$BillingAddress = $this->Customer->getBilling();
		}

// 		if ($ShippingAddress === null)
// 		{
// 			$ShippingAddress = $this->Customer->getShipping();
// 		}

		$this->BillingAddress = $BillingAddress;
		$this->ShippingAddress = $ShippingAddress;
	}

	/**
	 * Returns the form of address to use with the plentymarkets SOAP API
	 *
	 * @param string $key
	 * @return integer
	 */
	protected static function getFormOfAddress($key)
	{
		if (array_key_exists($key, self::$mappingFormOfAddress))
		{
			return self::$mappingFormOfAddress[$key];
		}
		return null;
	}

	/**
	 * Returns the country id to use with the plentymarkets SOAP API
	 *
	 * @param integer $countryID
	 * @return integer null
	 */
	protected static function getCountryID($countryID)
	{
		try
		{
			return PlentymarketsMappingController::getCountryByShopwareID($countryID);
		}
		catch (Exception $E)
		{
			return null;
		}
	}

	/**
	 * Exports the customer and the delivery address
	 */
	public function export()
	{
		$this->exportCustomer();
		$this->exportDeliveryAddress();
	}

	/**
	 * Exports the customer
	 */
	protected function exportCustomer()
	{
		if (is_null($this->BillingAddress))
		{
			return;
		}
		
		try
		{
			$this->PLENTY_customerID = PlentymarketsMappingController::getCustomerByShopwareID($this->BillingAddress->getId());
			return;
		}
		catch (PlentymarketsMappingExceptionNotExistant $E)
		{
		}

		$Request_AddCustomers = new PlentySoapRequest_AddCustomers();

		$Request_AddCustomers->Customers = array();

		$Object_AddCustomersCustomer = new PlentySoapObject_AddCustomersCustomer();
		$Object_AddCustomersCustomer->City = $this->BillingAddress->getCity();
		$Object_AddCustomersCustomer->Company = $this->BillingAddress->getCompany();
		$Object_AddCustomersCustomer->CountryID = $this->getBillingCountryID(); // int
		$Object_AddCustomersCustomer->CustomerNumber = $this->getCustomerNumber(); // string
		// Bug in shopware - $this->Customer->getGroup()->getId() always returns 0
		// $Object_AddCustomersCustomer->CustomerClass = PlentymarketsMappingController::getCustomerClassByShopwareID($this->Customer->getGroup()->getId());
		$Object_AddCustomersCustomer->CustomerSince = $this->Customer->getFirstLogin()->getTimestamp(); // int
		$Object_AddCustomersCustomer->Email = $this->Customer->getEmail(); // string
		$Object_AddCustomersCustomer->ExternalCustomerID = PlentymarketsUtils::getExternalCustomerID($this->Customer->getId()); // string
		$Object_AddCustomersCustomer->FormOfAddress = $this->getBillingFormOfAddress(); // string
		$Object_AddCustomersCustomer->Fax = $this->BillingAddress->getFax();
		$Object_AddCustomersCustomer->FirstName = $this->BillingAddress->getFirstName();
		$Object_AddCustomersCustomer->HouseNo = $this->BillingAddress->getStreetNumber();
		$Object_AddCustomersCustomer->IsBlocked = !$this->Customer->getActive();
		$Object_AddCustomersCustomer->Language = 'de';
		$Object_AddCustomersCustomer->Newsletter = (integer) $this->Customer->getNewsletter();
		$Object_AddCustomersCustomer->PayInvoice = true; // boolean
		$Object_AddCustomersCustomer->Street = $this->BillingAddress->getStreet();
		$Object_AddCustomersCustomer->Surname = $this->BillingAddress->getLastName();
		$Object_AddCustomersCustomer->Telephone = $this->BillingAddress->getPhone();
		$Object_AddCustomersCustomer->VAT_ID = $this->BillingAddress->getVatId();
		$Object_AddCustomersCustomer->ZIP = $this->BillingAddress->getZipCode();

		if ($this->BillingAddress->getAttribute() != null)
		{
			$Object_CustomerFreeTestFields = new PlentySoapObject_CustomerFreeTestFields();
			$Object_CustomerFreeTestFields->Free1 = $this->BillingAddress->getAttribute()->getText1();
			$Object_CustomerFreeTestFields->Free2 = $this->BillingAddress->getAttribute()->getText2();
			$Object_CustomerFreeTestFields->Free3 = $this->BillingAddress->getAttribute()->getText3();
			$Object_CustomerFreeTestFields->Free4 = $this->BillingAddress->getAttribute()->getText4();
			$Object_CustomerFreeTestFields->Free5 = $this->BillingAddress->getAttribute()->getText5();
			$Object_CustomerFreeTestFields->Free6 = $this->BillingAddress->getAttribute()->getText6();
			$Object_AddCustomersCustomer->FreeTextFields = $Object_CustomerFreeTestFields;
		}

		$Request_AddCustomers->Customers[] = $Object_AddCustomersCustomer;

		$Response_AddCustomers = PlentymarketsSoapClient::getInstance()->AddCustomers($Request_AddCustomers);

		if ($Response_AddCustomers->ResponseMessages->item[0]->Code == 100 || $Response_AddCustomers->ResponseMessages->item[0]->Code == 200)
		{
			$this->PLENTY_customerID = (integer) $Response_AddCustomers->ResponseMessages->item[0]->SuccessMessages->item[0]->Value;
			PlentymarketsMappingController::addCustomer($this->BillingAddress->getId(), $this->PLENTY_customerID);
		}
	}

	/**
	 * Exports the delivery address
	 */
	protected function exportDeliveryAddress()
	{
		if ($this->ShippingAddress === null)
		{
			return;
		}

		if ($this->PLENTY_customerID === null)
		{
			return;
		}

		$Request_AddCustomerDeliveryAddresses = new PlentySoapRequest_AddCustomerDeliveryAddresses();

		$Request_AddCustomerDeliveryAddresses->DeliveryAddresses = array();
		$Object_AddCustomerDeliveryAddressesCustomer = new PlentySoapObject_AddCustomerDeliveryAddressesCustomer();
		$Object_AddCustomerDeliveryAddressesCustomer->AdditionalName = null; // string
		$Object_AddCustomerDeliveryAddressesCustomer->City = $this->ShippingAddress->getCity();
		$Object_AddCustomerDeliveryAddressesCustomer->Company = $this->ShippingAddress->getCompany();
		$Object_AddCustomerDeliveryAddressesCustomer->CountryID = $this->getDeliveryCountryID(); // int
		$Object_AddCustomerDeliveryAddressesCustomer->CustomerID = $this->PLENTY_customerID; // int
		$Object_AddCustomerDeliveryAddressesCustomer->ExternalDeliveryAddressID = PlentymarketsUtils::getExternalCustomerID($this->ShippingAddress->getId()); // string
// 		$Object_AddCustomerDeliveryAddressesCustomer->Fax = $this->ShippingAddress->getF
		$Object_AddCustomerDeliveryAddressesCustomer->FirstName = $this->ShippingAddress->getFirstName();
		$Object_AddCustomerDeliveryAddressesCustomer->FormOfAddress = $this->getDeliveryFormOfAddress(); // int
		$Object_AddCustomerDeliveryAddressesCustomer->HouseNumber = $this->ShippingAddress->getStreetNumber();
		$Object_AddCustomerDeliveryAddressesCustomer->Street = $this->ShippingAddress->getStreet();
		$Object_AddCustomerDeliveryAddressesCustomer->Surname = $this->ShippingAddress->getLastName();
		$Object_AddCustomerDeliveryAddressesCustomer->ZIP = $this->ShippingAddress->getZipCode();

		$Request_AddCustomerDeliveryAddresses->DeliveryAddresses[] = $Object_AddCustomerDeliveryAddressesCustomer;

		$Response_AddCustomerDeliveryAddresses = PlentymarketsSoapClient::getInstance()->AddCustomerDeliveryAddresses($Request_AddCustomerDeliveryAddresses);

		$this->PLENTY_addressDispatchID = (integer) $Response_AddCustomerDeliveryAddresses->ResponseMessages->item[0]->SuccessMessages->item[0]->Value;
	}

	/**
	 * Returns a usable customer number
	 *
	 * @return string
	 */
	protected function getCustomerNumber()
	{
		if ($this->BillingAddress->getNumber() != '')
		{
			return $this->BillingAddress->getNumber();
		}
		else
		{
			return PlentymarketsUtils::getExternalCustomerID($this->Customer->getId());
		}
	}

	/**
	 * Returns the country id for the billing address
	 *
	 * @return integer
	 */
	protected function getBillingCountryID()
	{
		if (method_exists($this->BillingAddress, 'getCountryId'))
		{
			return self::getCountryID($this->BillingAddress->getCountryId());
		}
		else
		{
			return self::getCountryID($this->BillingAddress->getCountry()->getId());
		}
	}

	/**
	 * Returns the country id for the delivery address
	 *
	 * @return integer
	 */
	protected function getDeliveryCountryID()
	{
		if (method_exists($this->ShippingAddress, 'getCountryId'))
		{
			return self::getCountryID($this->ShippingAddress->getCountryId());
		}
		else
		{
			return self::getCountryID($this->ShippingAddress->getCountry()->getId());
		}
	}

	/**
	 * Returns the form of address for the billing address
	 *
	 * @return integer
	 */
	protected function getBillingFormOfAddress()
	{
		return self::getFormOfAddress($this->BillingAddress->getSalutation());
	}

	/**
	 * Returns the form of address for the delivery address
	 *
	 * @return integer
	 */
	protected function getDeliveryFormOfAddress()
	{
		return self::getFormOfAddress($this->ShippingAddress->getSalutation());
	}

	/**
	 * Returns the plentymarkets customer id
	 *
	 * @return integer
	 */
	public function getPlentyCustomerID()
	{
		return $this->PLENTY_customerID;
	}

	/**
	 * Returns the plentymarkes address dispatch id
	 *
	 * @return integer|null
	 */
	public function getPlentyAddressDispatchID()
	{
		return $this->PLENTY_addressDispatchID;
	}
}
