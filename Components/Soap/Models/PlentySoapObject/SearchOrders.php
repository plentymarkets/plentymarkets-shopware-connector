<?php

/**
 */
class PlentySoapObject_SearchOrders
{
	
	/**
	 * @var PlentySoapObject_CustomerAddress
	 */
	public $OrderCustomerAddress;
	
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
}
