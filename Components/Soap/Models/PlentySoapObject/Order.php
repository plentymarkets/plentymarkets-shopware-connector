<?php

/**
 */
class PlentySoapObject_Order
{
	
	/**
	 * @var PlentySoapObject_DeliveryAddress
	 */
	public $OrderDeliveryAddress;
	
	/**
	 * @var PlentySoapObject_OrderHead
	 */
	public $OrderHead;
	
	/**
	 * @var ArrayOfPlentysoapobject_orderitem
	 */
	public $OrderItems;
	
	/**
	 * @var int
	 */
	public $TemplateID;
}
