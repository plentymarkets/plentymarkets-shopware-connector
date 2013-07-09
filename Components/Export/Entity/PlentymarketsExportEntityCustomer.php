<?php
require_once __DIR__ . '/../../Soap/Models/PlentySoapObject/AddCustomersCustomer.php';
require_once __DIR__ . '/../../Soap/Models/PlentySoapObject/CustomerBankData.php';
require_once __DIR__ . '/../../Soap/Models/PlentySoapObject/CustomerFreeTestFields.php';
require_once __DIR__ . '/../../Soap/Models/PlentySoapRequest/AddCustomers.php';
require_once __DIR__ . '/../../Soap/Models/PlentySoapObject/AddCustomerDeliveryAddressesCustomer.php';
require_once __DIR__ . '/../../Soap/Models/PlentySoapRequest/AddCustomerDeliveryAddresses.php';

/**
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsExportEntityCustomer
{

	/**
	 *
	 * @var array
	 */
	protected $customer;

	/**
	 *
	 * @var array
	 */
	protected $billingAddress;

	/**
	 *
	 * @var array null address
	 */
	protected $deliveryAddress;

	/**
	 *
	 * @var integer
	 */
	protected $PLENTY_customerID;

	/**
	 *
	 * @var integer null
	 */
	protected $PLENTY_addressDispatchID;

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
	 *
	 * @param array $customer
	 * @param array $billingAddress
	 * @param array|null $deliveryAddress
	 */
	public function __construct(array $customer, array $billingAddress, $deliveryAddress = null)
	{
		$this->customer = $customer;
		$this->billingAddress = $billingAddress;
		$this->deliveryAddress = $deliveryAddress;
	}

	/**
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
	 */
	public function export()
	{
		$this->exportCustomer();
		$this->exportDeliveryAddress();
	}

	/**
	 */
	protected function exportCustomer()
	{
		$Request_AddCustomers = new PlentySoapRequest_AddCustomers();

		$Request_AddCustomers->Customers = array();

		$Object_AddCustomersCustomer = new PlentySoapObject_AddCustomersCustomer();
		$Object_AddCustomersCustomer->City = $this->billingAddress['city']; // string
		$Object_AddCustomersCustomer->Company = $this->billingAddress['company']; // string
		$Object_AddCustomersCustomer->CountryID = $this->getBillingCountryID(); // int
		$Object_AddCustomersCustomer->CustomerNumber = $this->getCustomerNumber(); // string
		$Object_AddCustomersCustomer->CustomerSince = $this->customer['firstLogin']->getTimestamp(); // int
		$Object_AddCustomersCustomer->Email = $this->customer['email']; // string
		$Object_AddCustomersCustomer->ExternalCustomerID = PlentymarketsUtils::getExternalCustomerID($this->customer['id']); // string
		$Object_AddCustomersCustomer->FormOfAddress = $this->getBillingFormOfAddress(); // string
		$Object_AddCustomersCustomer->Fax = $this->billingAddress['fax']; // string
		$Object_AddCustomersCustomer->FirstName = $this->billingAddress['firstName']; // string
		$Object_AddCustomersCustomer->HouseNo = $this->billingAddress['streetNumber']; // string
		$Object_AddCustomersCustomer->IsBlocked = !$this->customer['active']; // boolean
		$Object_AddCustomersCustomer->Language = 'de';
		$Object_AddCustomersCustomer->Newsletter = (integer) $this->customer['newsletter']; // int
		$Object_AddCustomersCustomer->PayInvoice = true; // boolean
		$Object_AddCustomersCustomer->Street = $this->billingAddress['street']; // string
		$Object_AddCustomersCustomer->Surname = $this->billingAddress['lastName']; // string
		$Object_AddCustomersCustomer->Telephone = $this->billingAddress['telephone']; // string
		$Object_AddCustomersCustomer->VAT_ID = $this->billingAddress['vatId']; // string
		$Object_AddCustomersCustomer->ZIP = $this->billingAddress['zipCode']; // string

		$Object_CustomerFreeTestFields = new PlentySoapObject_CustomerFreeTestFields();
		$Object_CustomerFreeTestFields->Free1 = $this->billingAddress['attribute']['text1']; // string
		$Object_CustomerFreeTestFields->Free2 = $this->billingAddress['attribute']['text2']; // string
		$Object_CustomerFreeTestFields->Free3 = $this->billingAddress['attribute']['text3']; // string
		$Object_CustomerFreeTestFields->Free4 = $this->billingAddress['attribute']['text4']; // string
		$Object_CustomerFreeTestFields->Free5 = $this->billingAddress['attribute']['text5']; // string
		$Object_CustomerFreeTestFields->Free6 = $this->billingAddress['attribute']['text6']; // string
		$Object_AddCustomersCustomer->FreeTextFields = $Object_CustomerFreeTestFields;

		$Request_AddCustomers->Customers[] = $Object_AddCustomersCustomer;

		$Response_AddCustomers = PlentymarketsSoapClient::getInstance()->AddCustomers($Request_AddCustomers);

		if ($Response_AddCustomers->ResponseMessages->item[0]->Code == 100 || $Response_AddCustomers->ResponseMessages->item[0]->Code == 200)
		{
			$this->PLENTY_customerID = (integer) $Response_AddCustomers->ResponseMessages->item[0]->SuccessMessages->item[0]->Value;
			PlentymarketsMappingController::addCustomer($this->customer['id'], $this->PLENTY_customerID);
		}
	}

	/**
	 */
	protected function exportDeliveryAddress()
	{
		if ($this->deliveryAddress === null)
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
		$Object_AddCustomerDeliveryAddressesCustomer->City = $this->deliveryAddress['city']; // string
		$Object_AddCustomerDeliveryAddressesCustomer->Company = $this->deliveryAddress['company']; // string
		$Object_AddCustomerDeliveryAddressesCustomer->CountryID = $this->getDeliveryCountryID(); // int
		$Object_AddCustomerDeliveryAddressesCustomer->CustomerID = $this->PLENTY_customerID; // int
		$Object_AddCustomerDeliveryAddressesCustomer->ExternalDeliveryAddressID = PlentymarketsUtils::getExternalCustomerID($this->deliveryAddress['id']); // string
		$Object_AddCustomerDeliveryAddressesCustomer->Fax = $this->deliveryAddress['fax']; // string
		$Object_AddCustomerDeliveryAddressesCustomer->FirstName = $this->deliveryAddress['firstName']; // string
		$Object_AddCustomerDeliveryAddressesCustomer->FormOfAddress = $this->getDeliveryFormOfAddress(); // int
		$Object_AddCustomerDeliveryAddressesCustomer->HouseNumber = $this->deliveryAddress['streetNumber']; // string
		$Object_AddCustomerDeliveryAddressesCustomer->Street = $this->deliveryAddress['street']; // string
		$Object_AddCustomerDeliveryAddressesCustomer->Surname = $this->deliveryAddress['lastName']; // string
		$Object_AddCustomerDeliveryAddressesCustomer->ZIP = $this->deliveryAddress['zipCode']; // string

		$Request_AddCustomerDeliveryAddresses->DeliveryAddresses[] = $Object_AddCustomerDeliveryAddressesCustomer;

		$Response_AddCustomerDeliveryAddresses = PlentymarketsSoapClient::getInstance()->AddCustomerDeliveryAddresses($Request_AddCustomerDeliveryAddresses);

		$this->PLENTY_addressDispatchID = (integer) $Response_AddCustomerDeliveryAddresses->ResponseMessages->item[0]->SuccessMessages->item[0]->Value;
	}

	/**
	 *
	 * @return string
	 */
	protected function getCustomerNumber()
	{
		if (!empty($this->billingAddress['number']))
		{
			return $this->billingAddress['number'];
		}
		else
		{
			return PlentymarketsUtils::getExternalCustomerID($this->customer['id']);
		}
	}

	/**
	 *
	 * @return integer
	 */
	protected function getBillingCountryID()
	{
		return self::getCountryID($this->billingAddress['countryId']);
	}

	/**
	 *
	 * @return integer
	 */
	protected function getDeliveryCountryID()
	{
		return self::getCountryID($this->deliveryAddress['countryId']);
	}

	/**
	 *
	 * @return integer
	 */
	protected function getBillingFormOfAddress()
	{
		return self::getFormOfAddress($this->billingAddress['salutation']);
	}

	/**
	 *
	 * @return integer
	 */
	protected function getDeliveryFormOfAddress()
	{
		return self::getFormOfAddress($this->deliveryAddress['salutation']);
	}

	/**
	 *
	 * @return integer
	 */
	public function getPlentyCustomerID()
	{
		return $this->PLENTY_customerID;
	}

	/**
	 *
	 * @return integer|null
	 */
	public function getPlentyAddressDispatchID()
	{
		return $this->PLENTY_addressDispatchID;
	}
}
