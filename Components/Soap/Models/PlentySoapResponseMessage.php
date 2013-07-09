<?php

/**
 */
class PlentySoapResponseMessage
{
	
	/**
	 * @var int
	 */
	public $Code;
	
	/**
	 * @var ArrayOfPlentysoapresponsesubmessage
	 */
	public $ErrorMessages;
	
	/**
	 * @var string
	 */
	public $IdentificationKey;
	
	/**
	 * @var string
	 */
	public $IdentificationValue;
	
	/**
	 * @var ArrayOfPlentysoapresponsesubmessage
	 */
	public $SuccessMessages;
	
	/**
	 * @var ArrayOfPlentysoapresponsesubmessage
	 */
	public $Warnings;
}
