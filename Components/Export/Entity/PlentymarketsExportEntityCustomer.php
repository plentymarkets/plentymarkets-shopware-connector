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
 * PlentymarketsExportEntityCustomer provides the actual customer export functionality. Like the other export
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
	 * @var array
	 */
	protected static $mappingFormOfAddress = array(
		'mr' => 0,
		'ms' => 1,
		'company' => 2
	);
	/**
	 *
	 * @var \Shopware\Models\Customer\Customer
	 */
	protected $Customer;

	/**
	 *
	 * @var unknown
	 */
	protected $BillingAddress;

	/**
	 *
	 * @var unknown
	 */
	protected $ShippingAddress;

	/**
	 *
	 * @var integer
	 */
	protected $PLENTY_customerID;

	/**
	 * Constructor method
	 *
	 * @param \Shopware\Models\Customer\Customer $Customer
	 * @param string $BillingAddress
	 * @param string $ShippingAddress
	 * @throws PlentymarketsExportEntityException
	 */
	public function __construct($Customer, $BillingAddress = null, $ShippingAddress = null)
	{
		$this->Customer = $Customer;

		// Try to load the customer
		try
		{
			$this->Customer->getEmail();
		}
		catch (Exception $E)
		{
			throw new PlentymarketsExportEntityException('The customer no longer exists', 2101);
		}

		if (!$this->Customer->getFirstLogin() instanceof DateTime)
		{
			throw new PlentymarketsExportEntityException('The customer no longer exists', 2102);
		}

		if ($BillingAddress === null)
		{
			$BillingAddress = $this->Customer->getBilling();
		}

		$this->BillingAddress = $BillingAddress;
		$this->ShippingAddress = $ShippingAddress;
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
			throw new PlentymarketsExportEntityException('The customer with the email address »' . $this->Customer->getEmail() . '« could not be exported (no billing address)', 2100);
		}

		try
		{
			if ($this->BillingAddress instanceof \Shopware\Models\Customer\Billing)
			{
				$this->PLENTY_customerID = PlentymarketsMappingController::getCustomerBillingAddressByShopwareID($this->BillingAddress->getId());
			}
			else if ($this->BillingAddress instanceof \Shopware\Models\Order\Billing)
			{
				$this->PLENTY_customerID = PlentymarketsMappingController::getCustomerByShopwareID($this->BillingAddress->getId());
			}

			// Already exported
			return;
		}
		catch (PlentymarketsMappingExceptionNotExistant $E)
		{
		}

		// Logging
		PlentymarketsLogger::getInstance()->message('Export:Customer', 'Export of the customer with the number »' . $this->getCustomerNumber() . '«');

		$city = trim($this->BillingAddress->getCity());
		
		// check for shopware version 	
		if(method_exists($this->BillingAddress, 'getStreetNumber'))
		{
			// shopware version 4

			$streetHouseNumber = trim($this->BillingAddress->getStreetNumber());
			$streetName = trim($this->BillingAddress->getStreet());
		}
		else
		{
			// shopware version 5
			$street_arr = PlentymarketsUtils::extractStreetAndHouseNo($this->BillingAddress->getStreet());

			if(isset($street_arr['street']) && strlen($street_arr['street']) > 0)
			{
				$streetName = $street_arr['street'];
			}
			else
			{
				$streetName = trim($this->BillingAddress->getStreet());
			}

			if(isset($street_arr['houseNo']) && strlen($street_arr['houseNo']) > 0)
			{
				$streetHouseNumber = $street_arr['houseNo'];
			}
			else
			{
				//no house number was found in the street string
				$streetHouseNumber = '';
			}
		}
		
		$zip = trim($this->BillingAddress->getZipCode());

		if (empty($city))
		{
			$city = PlentymarketsConfig::getInstance()->get('CustomerDefaultCity');
		}

		if (!isset($streetHouseNumber) || $streetHouseNumber == '')
		{
			$streetHouseNumber = PlentymarketsConfig::getInstance()->get('CustomerDefaultHouseNumber');
		}

		if (!isset($streetName) || $streetName == '')
		{
			$streetName = PlentymarketsConfig::getInstance()->get('CustomerDefaultStreet');
		}

		if ($zip == '')
		{
			$zip = PlentymarketsConfig::getInstance()->get('CustomerDefaultZipcode');
		}

		$Request_SetCustomers = new PlentySoapRequest_SetCustomers();

		$Request_SetCustomers->Customers = array();

		$Object_SetCustomersCustomer = new PlentySoapObject_Customer();
		$Object_SetCustomersCustomer->City = $city;
		$Object_SetCustomersCustomer->Company = $this->BillingAddress->getCompany();
		$Object_SetCustomersCustomer->CountryID = $this->getBillingCountryID(); // int
		$Object_SetCustomersCustomer->CustomerClass = $this->getCustomerClassId();
		$Object_SetCustomersCustomer->CustomerNumber = $this->getCustomerNumber(); // string
		$Object_SetCustomersCustomer->CustomerSince = $this->Customer->getFirstLogin()->getTimestamp(); // int
		$Object_SetCustomersCustomer->Email = $this->Customer->getEmail(); // string
		$Object_SetCustomersCustomer->ExternalCustomerID = PlentymarketsUtils::getExternalCustomerID($this->Customer->getId()); // string
		$Object_SetCustomersCustomer->FormOfAddress = $this->getBillingFormOfAddress(); // string
		$Object_SetCustomersCustomer->Fax = $this->BillingAddress->getFax();
		$Object_SetCustomersCustomer->FirstName = $this->BillingAddress->getFirstName();
		$Object_SetCustomersCustomer->HouseNo = $streetHouseNumber;
		$Object_SetCustomersCustomer->IsBlocked = !$this->Customer->getActive();
		$Object_SetCustomersCustomer->Newsletter = (integer) $this->Customer->getNewsletter();
		$Object_SetCustomersCustomer->PayInvoice = true; // boolean
		$Object_SetCustomersCustomer->Street = $streetName;
		$Object_SetCustomersCustomer->Surname = $this->BillingAddress->getLastName();
		$Object_SetCustomersCustomer->Telephone = $this->BillingAddress->getPhone();
		$Object_SetCustomersCustomer->VAT_ID = $this->BillingAddress->getVatId();
		$Object_SetCustomersCustomer->ZIP = $zip;

		// Store id
		try
		{
			$Object_SetCustomersCustomer->StoreID = PlentymarketsMappingController::getShopByShopwareID($this->Customer->getShop()->getId());
			$Object_SetCustomersCustomer->Language = strtolower(substr($this->Customer->getShop()->getLocale()->getLocale(), 0, 2));
		}
		catch (PlentymarketsMappingExceptionNotExistant $E)
		{
		}

		// Customer class
		if ($this->Customer->getGroup()->getId() > 0)
		{
			try
			{
				$Object_SetCustomersCustomer->CustomerClass = PlentymarketsMappingController::getCustomerClassByShopwareID($this->Customer->getGroup()->getId());
			}
			catch (PlentymarketsMappingExceptionNotExistant $E)
			{
			}
		}

		$Request_SetCustomers->Customers[] = $Object_SetCustomersCustomer;

		$Response_SetCustomers = PlentymarketsSoapClient::getInstance()->SetCustomers($Request_SetCustomers);

		if (!$Response_SetCustomers->Success)
		{
			throw new PlentymarketsExportEntityException('The customer with the number »' . $this->getCustomerNumber() . '« could not be exported', 2110);
		}

		if ($Response_SetCustomers->ResponseMessages->item[0]->Code == 100 || $Response_SetCustomers->ResponseMessages->item[0]->Code == 200)
		{
			$this->PLENTY_customerID = (integer) $Response_SetCustomers->ResponseMessages->item[0]->SuccessMessages->item[0]->Value;

			if ($this->BillingAddress instanceof \Shopware\Models\Customer\Billing)
			{
				PlentymarketsMappingController::addCustomerBillingAddress($this->BillingAddress->getId(), $this->PLENTY_customerID);
			}
			else if ($this->BillingAddress instanceof \Shopware\Models\Order\Billing)
			{
				PlentymarketsMappingController::addCustomer($this->BillingAddress->getId(), $this->PLENTY_customerID);
			}
		}
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
	 * Returns the form of address for the billing address
	 *
	 * @return integer
	 */
	protected function getBillingFormOfAddress()
	{
		return self::getFormOfAddress($this->BillingAddress->getSalutation());
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

		$city = trim($this->ShippingAddress->getCity());

		// check for shopware version 

		if(method_exists($this->ShippingAddress, 'getStreetNumber'))
		{
			// shopware version 4

			$streetHouseNumber = trim($this->ShippingAddress->getStreetNumber());
			$streetName = trim($this->ShippingAddress->getStreet());
		}
		else
		{
			// shopware version 5
			$street_arr = PlentymarketsUtils::extractStreetAndHouseNo($this->ShippingAddress->getStreet());

			if(isset($street_arr['street']) && strlen($street_arr['street']) > 0)
			{
				$streetName = $street_arr['street'];
			}
			else
			{
				$streetName = trim($this->ShippingAddress->getStreet());
			}

			if(isset($street_arr['houseNo']) && strlen($street_arr['houseNo']) > 0)
			{
				$streetHouseNumber = $street_arr['houseNo'];
			}
			else
			{
				$streetHouseNumber = '';
			}
		}
		
		$zip = trim($this->ShippingAddress->getZipCode());

		if (empty($city))
		{
			$city = PlentymarketsConfig::getInstance()->get('CustomerDefaultCity');
		}

		if (!isset($streetHouseNumber) || $streetHouseNumber == '')
		{
			$streetHouseNumber = PlentymarketsConfig::getInstance()->get('CustomerDefaultHouseNumber');
		}

		if (!isset($streetName) || $streetName == '')
		{
			$streetName = PlentymarketsConfig::getInstance()->get('CustomerDefaultStreet');
		}

		if ($zip == '')
		{
			$zip = PlentymarketsConfig::getInstance()->get('CustomerDefaultZipcode');
		}

		$Request_SetCustomerDeliveryAddresses = new PlentySoapRequest_SetCustomerDeliveryAddresses();

		$Request_SetCustomerDeliveryAddresses->DeliveryAddresses = array();
		$Object_SetCustomerDeliveryAddressesCustomer = new PlentySoapRequest_ObjectSetCustomerDeliveryAddresses();
		$Object_SetCustomerDeliveryAddressesCustomer->AdditionalName = null; // string
		$Object_SetCustomerDeliveryAddressesCustomer->City = $city;
		$Object_SetCustomerDeliveryAddressesCustomer->Company = $this->ShippingAddress->getCompany();
		$Object_SetCustomerDeliveryAddressesCustomer->CountryID = $this->getDeliveryCountryID(); // int
		$Object_SetCustomerDeliveryAddressesCustomer->CustomerID = $this->PLENTY_customerID; // int
		$Object_SetCustomerDeliveryAddressesCustomer->ExternalDeliveryAddressID = PlentymarketsUtils::getExternalCustomerID($this->ShippingAddress->getId()); // string
		$Object_SetCustomerDeliveryAddressesCustomer->FirstName = $this->ShippingAddress->getFirstName();
		$Object_SetCustomerDeliveryAddressesCustomer->FormOfAddress = $this->getDeliveryFormOfAddress(); // int
		$Object_SetCustomerDeliveryAddressesCustomer->HouseNumber = $streetHouseNumber;
		$Object_SetCustomerDeliveryAddressesCustomer->Street = $streetName;
		$Object_SetCustomerDeliveryAddressesCustomer->Surname = $this->ShippingAddress->getLastName();
		$Object_SetCustomerDeliveryAddressesCustomer->ZIP = $zip;

		$Request_SetCustomerDeliveryAddresses->DeliveryAddresses[] = $Object_SetCustomerDeliveryAddressesCustomer;

		$Response_SetCustomerDeliveryAddresses = PlentymarketsSoapClient::getInstance()->SetCustomerDeliveryAddresses($Request_SetCustomerDeliveryAddresses);

		if (!$Response_SetCustomerDeliveryAddresses->Success)
		{
			throw new PlentymarketsExportEntityException('The delivery address of the customer with the number »' . $this->getCustomerNumber() . '« could not be exported', 2120);
		}

		$this->PLENTY_addressDispatchID = (integer) $Response_SetCustomerDeliveryAddresses->ResponseMessages->item[0]->SuccessMessages->item[0]->Value;
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

	/**
	 * Returns the customer class id
	 *
	 * @return integer|null
	 */
	protected function getCustomerClassId()
	{
		try
		{
			return PlentymarketsMappingController::getCustomerClassByShopwareID($this->Customer->getGroup()->getId());
		}
		catch (Exception $E)
		{
			PyLog()->debug($E->getMessage());
			return null;
		}
	}

	
}
