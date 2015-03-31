<?php

class PlentySoapRequest_ObjectSetCustomerDeliveryAddresses {

	/**
	 * @var int
	 * @description Customer ID
	 * 
	 * @required
	 * 
	 * @minOccurs 1
	 * @maxOccurs 1
	 * @version 104
	 */
	public $CustomerID;
	
	/**
	 * @var int
	 * @description Delivery address ID
	 * 
	 * @minOccurs 0
	 * @maxOccurs 1
	 * @version 104
	 */
	public $DeliveryAddressID;
	
	/**
	 * @var String 
	 * @description External delivery address ID
	 * 
	 * @minOccurs 0
	 * @maxOccurs 1
	 * @version 104
	 */
	public $ExternalDeliveryAddressID;
	
	/**
	 * @var string
	 * @description Evaluation
	 * 
	 * @minOccurs 0
	 * @maxOccurs 1
	 * @version 104
	 */
	public $Evaluation;
	
	/**
	 * @var string
	 * @description Company
	 * 
	 * @minOccurs 0
	 * @maxOccurs 1
	 * @version 104
	 */
	public $Company;
	
	/**
	 * @var string
	 * @description Additional name
	 * 
	 * @minOccurs 0
	 * @maxOccurs 1
	 * @version 104
	 */
	public $AdditionalName;
	
	/**
	 * @var int
	 * @description Form of address
	 * @see Read the manual page <strong>important notes / <a href="en/soap-api/important-notes/" target="_blank">form of address</a></strong>
	 * 
	 * @valuation 0,1,2,3
	 * 
	 * @minOccurs 0
	 * @maxOccurs 1
	 * @version 104
	 */
	public $FormOfAddress;
	
	/**
	 * @var string
	 * @description First name
	 * 
	 * @required
	 * 
	 * @minOccurs 1
	 * @maxOccurs 1
	 * @version 104
	 */
	public $FirstName;
	
	/**
	 * @var string
	 * @description Surname
	 * 
	 * @required
	 * 
	 * @minOccurs 1
	 * @maxOccurs 1
	 * @version 104
	 */
	public $Surname;
	
	/**
	 * @var string
	 * @description Street
	 * 
	 * @required
	 * 
	 * @minOccurs 1
	 * @maxOccurs 1
	 * @version 104
	 */
	public $Street;
	
	/**
	 * @var string
	 * @description House number
	 * 
	 * @required
	 * 
	 * @minOccurs 1
	 * @maxOccurs 1
	 * @version 104
	 */
	public $HouseNumber;
	
	/**
	 * @var string
	 * @description ZIP
	 * 
	 * @required
	 * 
	 * @minOccurs 1
	 * @maxOccurs 1
	 * @version 104
	 */
	public $ZIP;
	
	/**
	 * @var string
	 * @description City
	 * 
	 * @required
	 * 
	 * @minOccurs 1
	 * @maxOccurs 1
	 * @version 104
	 */
	public $City;

	/**
	 * @var string
	 * @description Country state ISO code
	 *
	 * @minOccurs 0
	 * @maxOccurs 1
	 * @version 114
	 */
	public $StateISOCode;
	
	/**
	 * @var int 
	 * @description Country ID
	 * @info See SOAP-Call: <b>GetCountriesOfDelivery</b>
	 * 
	 * @required 
	 * 
	 * @minOccurs 1
	 * @maxOccurs 1
	 * @version 104
	 */
	public $CountryID;
	
	/**
	 * @var string
	 * @description Telephone number
	 * 
	 * @minOccurs 0
	 * @maxOccurs 1
	 * @version 104
	 */
	public $Telephone;
	
	/**
	 * @var string
	 * @description Fax number
	 * 
	 * @minOccurs 0
	 * @maxOccurs 1
	 * @version 104
	 */
	public $Fax;
	
	/**
	 * @var string
	 * @description E-mail address
	 * 
	 * @minOccurs 0
	 * @maxOccurs 1
	 * @version 104
	 */
	public $Email;
	
	/**
	 * @var string
	 * @description Postident
	 *
	 * @minOccurs 0
	 * @maxOccurs 1
	 * @version 110
	 */
	public $Postident;

	/**
	 * @var string
	 * @description PackstationNr
	 *
	 * @minOccurs 0
	 * @maxOccurs 1
	 * @version 110
	 */
	public $PackstationNr;

	/**
	 * @var string
	 * @description VAT Number
	 *
	 * @minOccurs 0
	 * @maxOccurs 1
	 * @version 110
	 */
	public $VAT_number;
	
}

?>