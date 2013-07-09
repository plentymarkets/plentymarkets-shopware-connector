<?php

/**
 */
class PlentySoapRequest_AddReorder
{
	
	/**
	 * @var int
	 */
	public $DeliveryTimestamp;
	
	/**
	 * @var int
	 */
	public $MarkingID;
	
	/**
	 * @var int
	 */
	public $PaymentTimestamp;
	
	/**
	 * @var int
	 */
	public $ReorderID;
	
	/**
	 * @var ArrayOfPlentysoapobject_addreorderitem
	 */
	public $ReorderItems;
	
	/**
	 * @var float
	 */
	public $ReorderStatus;
	
	/**
	 * @var int
	 */
	public $ResponsibleID;
	
	/**
	 * @var int
	 */
	public $SupplierID;
	
	/**
	 * @var string
	 */
	public $SupplierSign;
	
	/**
	 * @var int
	 */
	public $WarehouseID;
}
