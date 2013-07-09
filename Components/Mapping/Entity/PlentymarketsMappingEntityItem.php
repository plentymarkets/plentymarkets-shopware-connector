<?php
require_once __DIR__ . '/PlentymarketsMappingEntityAbstract.php';

/**
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsMappingEntityItem extends PlentymarketsMappingEntityAbstract
{

	/**
	 *
	 * @see PlentymarketsMappingEntityAbstract::getName()
	 */
	protected function getName()
	{
		return 'plenty_mapping_item';
	}
}
