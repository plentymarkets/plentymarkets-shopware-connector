<?php
require_once PY_SOAP . 'Models/PlentySoapRequest/GetPropertyGroups.php';
require_once PY_SOAP . 'Models/PlentySoapRequest/GetProperties.php';
require_once PY_COMPONENTS . 'Import/Entity/PlentymarketsImportEntityItemPropertyGroup.php';
require_once PY_COMPONENTS . 'Import/Entity/PlentymarketsImportEntityItemPropertyOption.php';

class PlentymarketsImportControllerItemProperty
{

	/**
	 * Performs the actual import
	 */
	public function run($lastUpdateTimestamp)
	{
		$Request_GetPropertyGroups = new PlentySoapRequest_GetPropertyGroups();
		$Request_GetPropertyGroups->Lang = 'de';
		$Request_GetPropertyGroups->LastUpdateFrom = $lastUpdateTimestamp;
		$Request_GetPropertyGroups->Page = 0;

		do
		{
			$Response_GetPropertyGroups = PlentymarketsSoapClient::getInstance()->GetPropertyGroups($Request_GetPropertyGroups);
			$Response_GetPropertyGroups instanceof PlentySoapResponse_GetPropertyGroups;

			foreach ($Response_GetPropertyGroups->PropertyGroups->item as $Option)
			{
				$PlentymarketsImportEntityItemPropertyGroup = new PlentymarketsImportEntityItemPropertyGroup($Option);
				$PlentymarketsImportEntityItemPropertyGroup->import();
			}
		}
		while (++$Request_GetPropertyGroups->Page < $Response_GetPropertyGroups->Pages);

		$Request_GetProperties = new PlentySoapRequest_GetProperties();
		$Request_GetProperties->Lang = 'de';
		$Request_GetProperties->LastUpdateFrom = $lastUpdateTimestamp;
		$Request_GetProperties->Page = 0;

		do
		{
			$Response_GetProperties = PlentymarketsSoapClient::getInstance()->GetProperties($Request_GetProperties);
			$Response_GetProperties instanceof PlentySoapResponse_GetProperties;

			foreach ($Response_GetProperties->Properties->item as $Option)
			{
				$PlentymarketsImportEntityItemPropertyOption = new PlentymarketsImportEntityItemPropertyOption($Option);
				$PlentymarketsImportEntityItemPropertyOption->import();
			}
		}
		while (++$Request_GetProperties->Page < $Response_GetProperties->Pages);
	}
}
