<?php
require_once __DIR__ . '/../../Soap/Models/PlentySoapRequest/AddLinkedItems.php';
require_once __DIR__ . '/../../Soap/Models/PlentySoapObject/AddLinkedItems.php';

/**
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsExportEntityItemLinked
{

	/**
	 *
	 * @var Shopware\Models\Article\Article
	 */
	protected $SHOPWARE_Article;

	/**
	 *
	 * @param Shopware\Models\Article\Article $Article
	 */
	public function __construct(Shopware\Models\Article\Article $Article)
	{
		$this->SHOPWARE_Article = $Article;
	}

	/**
	 *
	 */
	public function link()
	{
		$Request_AddLinkedItems = new PlentySoapRequest_AddLinkedItems();
		$Request_AddLinkedItems->CrosssellingList = array();

		foreach ($this->SHOPWARE_Article->getSimilar() as $Similar)
		{
			$Object_AddLinkedItems = new PlentySoapObject_AddLinkedItems();
			$Object_AddLinkedItems->Relationship = 'Similar'; // string
			$Object_AddLinkedItems->CrossItemSKU = PlentymarketsMappingController::getItemByShopwareID($Similar->getId()); // string
			$Request_AddLinkedItems->CrosssellingList[] = $Object_AddLinkedItems;
		}

		foreach ($this->SHOPWARE_Article->getRelated() as $Related)
		{
			$Object_AddLinkedItems = new PlentySoapObject_AddLinkedItems();
			$Object_AddLinkedItems->Relationship = 'Accessory'; // string
			$Object_AddLinkedItems->CrossItemSKU = PlentymarketsMappingController::getItemByShopwareID($Related->getId());
			$Request_AddLinkedItems->CrosssellingList[] = $Object_AddLinkedItems;
		}

		if (!count($Request_AddLinkedItems->CrosssellingList))
		{
			return;
		}

		$Request_AddLinkedItems->MainItemSKU = PlentymarketsMappingController::getItemByShopwareID($this->SHOPWARE_Article->getId()); // string

		// Do the request
		$Response_AddLinkedItems = PlentymarketsSoapClient::getInstance()->AddLinkedItems($Request_AddLinkedItems);
	}
}
