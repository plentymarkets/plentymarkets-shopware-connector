<?php

/**
 */
class PlentySoapResponse_AddClientOrder
{
	
	/**
	 * @var PlentySoapObject_AddClientOrderCustomerAddress
	 */
	public $OrderCustomerAddress;
	
	/**
	 * @var PlentySoapObject_AddClientOrderDeliveryAddress
	 */
	public $OrderDeliveryAddress;
	
	/**
	 * @var PlentySoapObject_AddClientOrderOrderHead
	 */
	public $OrderHead;
	
	/**
	 * @var ArrayOfPlentysoapobject_addclientorderorderitem
	 */
	public $OrderItems;
	
	/**
	 * @var ArrayOfPlentysoapresponsemessage
	 */
	public $ResponseMessages;
	
	/**
	 * @var boolean
	 */
	public $Success;
}
