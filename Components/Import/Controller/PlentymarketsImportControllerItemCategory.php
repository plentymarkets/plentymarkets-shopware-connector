<?php
require_once PY_SOAP . 'Models/PlentySoapRequest/GetItemCategoryCatalogBase.php';
require_once PY_COMPONENTS . 'Import/Entity/PlentymarketsImportEntityItemCategory.php';

class PlentymarketsImportControllerItemCategory
{
	public function __construct()
	{
		Shopware()->Db()->exec('
			CREATE TABLE IF NOT EXISTS `plenty_category` (
			  `plentyId` int(11) unsigned NOT NULL,
			  `plentyPath` varchar(255) NOT NULL DEFAULT "",
			  `shopwareId` int(11) unsigned NOT NULL,
			  `size` tinyint(4) unsigned NOT NULL,
			  KEY (`plentyId`)
			) ENGINE=MEMORY
		');
// 		Shopware()->Db()->exec('
// 			CREATE TEMPORARY TABLE `plenty_category` (
// 			  `plentyId` int(11) unsigned NOT NULL,
// 			  `plentyPath` varchar(255) NOT NULL DEFAULT "",
// 			  `shopwareId` int(11) unsigned NOT NULL,
//			  `size` tinyint(4) unsigned NOT NULL,
// 			  KEY (`plentyId`)
// 			) ENGINE=MEMORY
// 		');

		$Handle = Shopware()->Db()->query('
			SELECT * FROM plenty_mapping_category
		');

		while (($row = $Handle->fetch()) && $row)
		{
			$plentyIds = explode(';', $row['plentyID']);

			foreach ($plentyIds as $plentyId)
			{
				Shopware()->Db()->insert('plenty_category', array(
					'plentyId' => $plentyId,
					'plentyPath' => $row['plentyID'],
					'shopwareId' => $row['shopwareID'],
					'size' => count($plentyIds)
				));
			}
		}

	}

	public function __destruct()
	{
// 		Shopware()->Db()->exec('
// 			DROP TEMPORARY TABLE `plenty_category`
// 		');
	}

	public function run($lastUpdateTimestamp)
	{
		$Request_GetItemCategoryCatalogBase = new PlentySoapRequest_GetItemCategoryCatalogBase();
		$Request_GetItemCategoryCatalogBase->Lang = 'de';
		$Request_GetItemCategoryCatalogBase->LastUpdateFrom = $lastUpdateTimestamp;
		$Request_GetItemCategoryCatalogBase->Level = 1;

		do
		{

			$Request_GetItemCategoryCatalogBase->Page = 0;

			do
			{

				$Response_GetItemCategoryCatalogBase = PlentymarketsSoapClient::getInstance()->GetItemCategoryCatalogBase($Request_GetItemCategoryCatalogBase);
				$Response_GetItemCategoryCatalogBase instanceof PlentySoapResponse_GetItemCategoryCatalogBase;

				foreach ($Response_GetItemCategoryCatalogBase->Categories->item as $Category)
				{
					$PlentymarketsImportEntityItemCategory = new PlentymarketsImportEntityItemCategory($Category);
					$PlentymarketsImportEntityItemCategory->import();
				}
			}

			while (++$Request_GetItemCategoryCatalogBase->Page < $Response_GetItemCategoryCatalogBase->Pages);
		}
		while (++$Request_GetItemCategoryCatalogBase->Level <= 6);
	}
}
