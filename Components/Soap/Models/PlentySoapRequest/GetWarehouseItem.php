<?php

/**
 */
class PlentySoapRequest_GetWarehouseItem
{
	
	/**
	 * @var boolean
	 */
	public $GetAllLocationsWithStock;
	
	/**
	 * @var boolean
	 */
	public $GetFreeLocation;
	
	/**
	 * @var boolean
	 */
	public $GetPreviousLocation;
	
	/**
	 * @var string
	 */
	public $ItemEAN;
	
	/**
	 * @var string
	 */
	public $ItemNumber;
	
	/**
	 * @var string
	 */
	public $Lang;
	
	/**
	 * @var PlentySoapObject_GetWarehouseItemRefundLocation
	 */
	public $Refused;
	
	/**
	 * @var string
	 */
	public $SKU;
	
	/**
	 * @var int
	 */
	public $WarehouseId;
}
