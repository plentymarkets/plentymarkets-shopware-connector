<?php

require_once PY_SOAP . 'Models/PlentySoapRequest/GetItemAttributes.php';
require_once PY_COMPONENTS . 'Import/Entity/PlentymarketsImportEntityItemAttribute.php';

/**
 * Imports the item producers
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsImportControllerItemAttribute
{
	/**
	 * Performs the actual import
	 */
	public function run($lastUpdateTimestamp)
	{

		$Request_GetItemAttributes = new PlentySoapRequest_GetItemAttributes();
		$Request_GetItemAttributes->GetValues = true;
		$Request_GetItemAttributes->LastUpdateFrom = $lastUpdateTimestamp;

		$Response_GetItemAttributes = PlentymarketsSoapClient::getInstance()->GetItemAttributes($Request_GetItemAttributes);
		$Response_GetItemAttributes instanceof PlentySoapResponse_GetItemAttributes;

		if (!$Response_GetItemAttributes->Success)
		{
			return;
		}

		foreach ($Response_GetItemAttributes->Attributes->item as $Attribute)
		{
			$PlentymarketsImportEntityItemAttribute = new PlentymarketsImportEntityItemAttribute($Attribute);
			$PlentymarketsImportEntityItemAttribute->import();
		}
	}
}

