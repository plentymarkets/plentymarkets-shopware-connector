<?php
/**
 * Created by IntelliJ IDEA.
 * User: dbaechtle
 * Date: 06.01.14
 * Time: 11:51
 */

require_once PY_COMPONENTS . 'Import/Entity/Order/PlentymarketsImportEntityItemBundle.php';
require_once PY_SOAP . 'Models/PlentySoapRequest/GetItemBundles.php';

class PlentymarketsImportControllerItemBundle
{
	public function __construct()
	{
		PlentymarketsUtils::registerBundleModules();
	}

	public function import()
	{
		// Get all bundles
		$Request_GetItemBundles = new PlentySoapRequest_GetItemBundles();
		$Request_GetItemBundles->LastUpdate = 0;
		$Request_GetItemBundles->Page = 0;

		do {

			/** @var $Response_GetItemBundles PlentySoapResponse_GetItemBundles */
			$Response_GetItemBundles = PlentymarketsSoapClient::getInstance()->GetItemBundles($Request_GetItemBundles);

			foreach ($Response_GetItemBundles->ItemBundles->item as $PlentySoapObject_Bundle)
			{
				$PlentymarketsImportEntityItemBundle = new PlentymarketsImportEntityItemBundle($PlentySoapObject_Bundle);
				$PlentymarketsImportEntityItemBundle->import();
			}
		}

		// Until all pages are received
		while (++$Request_GetItemBundles->Page < $Response_GetItemBundles->Pages);
	}
}
