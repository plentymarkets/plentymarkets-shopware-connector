<?php

/**
 */
class PlentySoapRequest_GetStockMovements
{
	
	/**
	 * @var int
	 */
	public $DateFrom;
	
	/**
	 * @var int
	 */
	public $DateTo;
	
	/**
	 * @var boolean
	 */
	public $GetIncomingItems;
	
	/**
	 * @var boolean
	 */
	public $GetOutgoingItems;
	
	/**
	 * @var boolean
	 */
	public $GetStockCorrections;
	
	/**
	 * @var int
	 */
	public $Page;
	
	/**
	 * @var int
	 */
	public $Reason;
	
	/**
	 * @var string
	 */
	public $SKU;
	
	/**
	 * @var int
	 */
	public $WarehouseID;
}
