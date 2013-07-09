<?php

/**
 */
class PlentySoapRequest_GetWarehouseStorageLocation
{
	
	/**
	 * @var boolean
	 */
	public $GetFreeLocation;
	
	/**
	 * @var boolean
	 */
	public $GetPreviousLocation;
	
	/**
	 * @var PlentySoapObject_GetWarehouseStorageLocationRefused
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
