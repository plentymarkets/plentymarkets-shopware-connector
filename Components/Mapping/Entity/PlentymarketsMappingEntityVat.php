<?php
require_once __DIR__ . '/PlentymarketsMappingEntityAbstract.php';

/**
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsMappingEntityVat extends PlentymarketsMappingEntityAbstract
{

	/**
	 *
	 * @see PlentymarketsMappingEntityAbstract::init()
	 */
	protected function init()
	{
		parent::init();
		parent::initData();
	}

	/**
	 *
	 * @see PlentymarketsMappingEntityAbstract::getName()
	 */
	protected function getName()
	{
		return 'plenty_mapping_vat';
	}
}
