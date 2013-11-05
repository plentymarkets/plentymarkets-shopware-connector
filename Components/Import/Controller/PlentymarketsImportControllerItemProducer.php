<?php

require_once PY_SOAP . 'Models/PlentySoapRequest/GetProducers.php';
require_once PY_COMPONENTS . 'Import/Entity/PlentymarketsImportEntityItemProducer.php';

/**
 * Imports the item producers
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsImportControllerItemProducer
{
	/**
	 * Performs the actual import
	 */
	public function run($lastUpdateTimestamp)
	{
		$Request_GetProducers = new PlentySoapRequest_GetProducers();
		$Request_GetProducers->LastUpdateFrom = $lastUpdateTimestamp; // int

		// Do the request
		$Response_GetProducers = PlentymarketsSoapClient::getInstance()->GetProducers($Request_GetProducers);
		$Response_GetProducers instanceof PlentySoapResponse_GetProducers;

		foreach ($Response_GetProducers->Producers->item as $Producer)
		{
			$PlentymarketsImportEntityItemProducer = new PlentymarketsImportEntityItemProducer($Producer);
			$PlentymarketsImportEntityItemProducer->import();
		}
	}
}

