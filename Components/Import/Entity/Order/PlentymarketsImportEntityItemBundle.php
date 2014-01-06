<?php

require_once PY_SOAP . 'Models/PlentySoapRequest/GetItemsBase.php';

/**
 * Created by IntelliJ IDEA.
 * User: dbaechtle
 * Date: 06.01.14
 * Time: 11:52
 */
class PlentymarketsImportEntityItemBundle
{
	protected $SHOPWARE_bundleItemDetailList = array();

	protected $PLENTY_bundle;

	protected $PLENTY_bundleHeadId;

	/**
	 * @param PlentySoapObject_Bundle $PlentySoapObject_Bundle
	 */
	public function __construct($PlentySoapObject_Bundle)
	{
		$this->PLENTY_bundle = $PlentySoapObject_Bundle;
	}

	public function import()
	{
		$this->index();
		$this->importBundle();
	}

	protected function index()
	{
		$bundleHeadSku = explode('-', $this->PLENTY_bundle->SKU);
		$this->PLENTY_bundleHeadId = (integer) $bundleHeadSku[0];

		// Check whether all bundle items are present in shopware
		foreach ($this->PLENTY_bundle->Items->item as $PlentySoapObject_BundleItem)
		{
			/** @var $PlentySoapObject_BundleItem PlentySoapObject_BundleItem */
			$bundleItemSku = explode('-', $PlentySoapObject_BundleItem->SKU);
			$plentyBundleItemId = $bundleItemSku[0];

			try
			{
				// Variant
				if (isset($bundleItemSku[2]) && $bundleItemSku[2] > 0)
				{
					$shopwareBundleItemDetailId = PlentymarketsMappingController::getItemVariantByPlentyID($PlentySoapObject_BundleItem->SKU);

					// The detail is needed
					$detail = Shopware()->Models()->find('Shopware\Models\Article\Detail', $shopwareBundleItemDetailId);
					$isVariant = true;
				}

				// Base item
				else
				{
					$shopwareBundleItemId = PlentymarketsMappingController::getItemByPlentyID($plentyBundleItemId);

					/** @var $shopwareItem Shopware\Models\Article\Article */
					$shopwareItem = Shopware()->Models()->find('Shopware\Models\Article\Article', $shopwareBundleItemId);

					// The detail is needed
					$detail = $shopwareItem->getMainDetail();
					$isVariant = false;
				}
			}
			catch (PlentymarketsMappingExceptionNotExistant $E)
			{
				throw new Exception('Bundle kann nicht verarbeitet werden, danicht alle Paketartikel in shopware vorhanden sind');
			}

			$this->SHOPWARE_bundleItemDetailList[$detail->getId()] = array(
				'detail' => $detail,
				'quantity' => (integer) $PlentySoapObject_BundleItem->Quantity,
				'isVariant' => $isVariant
			);
		}
	}

	protected function importBundle()
	{
		/** @var $this ->PLENTY_bundle PlentySoapObject_Bundle */

		// Get the bundle head
		$Request_GetItemsBase = new PlentySoapRequest_GetItemsBase();
		$Request_GetItemsBase->GetAttributeValueSets = false;
		$Request_GetItemsBase->GetCategories = false;
		$Request_GetItemsBase->GetCategoryNames = false;
		$Request_GetItemsBase->GetItemAttributeMarkup = false;
		$Request_GetItemsBase->GetItemOthers = false;
		$Request_GetItemsBase->GetItemProperties = false;
		$Request_GetItemsBase->GetItemSuppliers = false;
		$Request_GetItemsBase->GetItemURL = 0;
		$Request_GetItemsBase->GetLongDescription = false;
		$Request_GetItemsBase->GetMetaDescription = false;
		$Request_GetItemsBase->GetShortDescription = false;
		$Request_GetItemsBase->GetTechnicalData = false;
		$Request_GetItemsBase->ItemID = $this->PLENTY_bundleHeadId;
		$Request_GetItemsBase->Page = 0;

		/** @var $Response_GetItemsBase PlentySoapResponse_GetItemsBase */
		$Response_GetItemsBase = PlentymarketsSoapClient::getInstance()->GetItemsBase($Request_GetItemsBase);

		if ($Response_GetItemsBase->Success == false || !isset($Response_GetItemsBase->ItemsBase->item[0]))
		{
			throw new Exception('Plentymarkets bundle head kann nicht abgerufen werden');
		}

		/** @var $ItemBase PlentySoapObject_ItemBase */
		$ItemBase = $Response_GetItemsBase->ItemsBase->item[0];

		try
		{
			// Get the existing bundle
			$shopwareBundleId = PlentymarketsMappingController::getItemBundleByPlentyID($this->PLENTY_bundleHeadId);

			/** @var $Bundle Shopware\CustomModels\Bundle\Bundle */
			$Bundle = Shopware()->Models()->find('Shopware\CustomModels\Bundle\Bundle', $shopwareBundleId);

			$currentShopwareBundleHeadItemDetailId = $Bundle->getArticle()->getMainDetail()->getId();
			if (!isset($this->SHOPWARE_bundleItemDetailList[$currentShopwareBundleHeadItemDetailId]))
			{
				// If the item which is the bundle head in shopware
				// has been removed in plentymarkets, the bundle has to get a new
				// head item. If this is not possible, the bundle will be delete.
				try
				{
					$shopwareBundleItemId = $this->getShopwareBundleItemId();

					$Article = Shopware()->Models()->find('Shopware\Models\Article\Article', $shopwareBundleItemId);
					PyLog()->message('Sync:Item:Bundle', 'The item x with the number x will be set as the new head of the bundle with the number ' . $Bundle->getNumber());

					$Bundle->setArticle($Article);
				}
				catch (Exception $e)
				{
					PlentymarketsMappingController::deleteItemBundleByShopwareID($Bundle->getId());

					PyLog()->message('Sync:Item:Bundle', 'Thje biind ewill be edeleld ' . $Bundle->getNumber());

					// Delete the bundle
					Shopware()->Models()->remove($Bundle);
					Shopware()->Models()->flush();

					throw $e;
				}
			}

			$action = 'update';
		}
		catch (PlentymarketsMappingExceptionNotExistant $E)
		{
			$action = 'create';

			// Create a new one
			$Bundle = new Shopware\CustomModels\Bundle\Bundle();
			$shopwareBundleItemId = $this->getShopwareBundleItemId();

			$Article = Shopware()->Models()->find('Shopware\Models\Article\Article', $shopwareBundleItemId);

			PyLog()->message('Sync:Item:Bundle', 'Using ' . $Article->getName() . ' as head of the bundle');

			// Set the stuff which needs to be set just one
			$Bundle->setArticle($Article);
			$Bundle->setType(1);
			$Bundle->setDiscountType('abs');
			$Bundle->setQuantity(0);
			$Bundle->setCreated();
			$Bundle->setSells(0);
		}

		//
		$Bundle->setName($ItemBase->Texts->Name);
		$Bundle->setNumber($ItemBase->ItemNo);

		$isLimited = $ItemBase->Stock->Limitation == 1;
		$Bundle->setLimited($isLimited);

		$isActive = $ItemBase->Availability->Inactive == 0 && $ItemBase->Availability->Webshop == 1;
		$Bundle->setActive($isActive);

		/**@var $CG Shopware\Models\Customer\Group */
		$CG = $this->getCustomerGroup();

		$shopwareBundleHeadItemId = $Bundle->getArticle()->getId();
		$items = array();
		foreach ($Bundle->getArticles() as $item)
		{
			/** @var $item Shopware\CustomModels\Bundle\Article */
			$itemDetailId = $item->getArticleDetail()->getId();

			// Not in the bundle or already done
			if (!isset($this->SHOPWARE_bundleItemDetailList[$itemDetailId]))
			{
				continue;
			}

			$quantity = $this->SHOPWARE_bundleItemDetailList[$itemDetailId]['quantity'];

			// If it is also the main item, the quantity needs to be reduced by one
			if ($item->getArticleDetail()->getArticle()->getId() == $shopwareBundleHeadItemId)
			{
				// If there is just one, the item is skipped since it is the HEAD - it will not be added as an item
				if ($quantity == 1)
				{
					unset($this->SHOPWARE_bundleItemDetailList[$itemDetailId]);
					continue;
				}

				// If the amount is higher - reduce item - the item is the HEAD and inside the bundle
				// in plenty it is just inside the bundle
				$quantity -= 1;
			}

			// Unset the detail - the rest of this array will be added as new items to the bundle
			unset($this->SHOPWARE_bundleItemDetailList[$itemDetailId]);

			// Update the quantity if changed
			if ($item->getQuantity() != $quantity)
			{
				$item->setQuantity($quantity);
			}

			$items[] = $item;
		}

		// Add all items, which aren't yet in the bundle
		foreach ($this->SHOPWARE_bundleItemDetailList as $config)
		{
			/** @var $detail Shopware\Models\Article\Detail */
			$detail = $config['detail'];

			// If the head is inside the bundle too, the amount needs to be reduced
			if ($detail->getArticle()->getId() == $shopwareBundleHeadItemId)
			{
				if ($config['quantity'] > 1)
				{
					$config['quantity'] -= 1;
				}

				// or skipped if it is just the one (only happens with new or reset bundles)
				else if ($config['quantity'] == 1)
				{
					continue;
				}
			}
			$ArticleNew = new Shopware\CustomModels\Bundle\Article();

			$ItemDetail = $detail;
			$quantity = $config['quantity'];

			$ArticleNew->setArticleDetail($ItemDetail);
			$ArticleNew->setQuantity($quantity);

			$items[] = $ArticleNew;
		}

		// Set the bundle items
		$Bundle->setArticles($items);

		$isPriceFound = false;
		$prices = array();
		foreach ($Bundle->getPrices() as $price)
		{
			/** @var $price Shopware\CustomModels\Bundle\Price */
			if ($price->getCustomerGroup()->getKey() == $CG->getKey())
			{
				$price->setDisplayPrice($ItemBase->PriceSet->Price);
				$price->setPrice($ItemBase->PriceSet->Price);
				$isPriceFound = true;
			}
			$prices[] = $price;
		}

		if (!$isPriceFound)
		{
			$Price = new Shopware\CustomModels\Bundle\Price();
			$Price->setBundle($Bundle);
			$Price->setCustomerGroup($CG);
			$Price->setDisplayPrice($ItemBase->PriceSet->Price);
			$Price->setPrice($ItemBase->PriceSet->Price);
			$prices[] = $Price;
			$Bundle->setPrices($prices);
		}

		$Bundle->setCustomerGroups(array($CG));

		Shopware()->Models()->persist($Bundle);
		Shopware()->Models()->flush();

		if ($action == 'create')
		{
			PlentymarketsMappingController::addItemBundle($Bundle->getId(), $this->PLENTY_bundleHeadId);
			PyLog()->message('Sync:Item:Bundle', 'The item bundle »' . $ItemBase->Texts->Name . '« with the number »' . $ItemBase->ItemNo . '« has been created');
		}
		else
		{
			PyLog()->message('Sync:Item:Bundle', 'The item bundle »' . $ItemBase->Texts->Name . '« with the number »' . $ItemBase->ItemNo . '« has been updated');
		}
	}

	/**
	 * Returns a shopware item detail id to use as bundle head
	 *
	 * @return int
	 * @throws Exception
	 */
	private function getShopwareBundleItemId()
	{
		$shopwareBundleItemId = null;

		// 1. attempt - a base item with the quantity of 1
		foreach ($this->SHOPWARE_bundleItemDetailList as $itemDetailId => $itemDetail)
		{
			if ($itemDetail['quantity'] == 1 && !$itemDetail['isVariant'])
			{
				$shopwareBundleItemId = $itemDetailId;
				break;
			}
		}

		// 2. attempt - a base item with any quantity
		if (!$shopwareBundleItemId)
		{
			foreach ($this->SHOPWARE_bundleItemDetailList as $itemDetailId => $itemDetail)
			{
				if (!$itemDetail['isVariant'])
				{
					$shopwareBundleItemId = $itemDetailId;
					break;
				}
			}
		}

		// nothing was found - the bundle cannot be (re-)created
		if (!$shopwareBundleItemId)
		{
			throw new Exception('Keine nicht Variante abei');
		}

		return $shopwareBundleItemId;
	}

	/**
	 * Returns the customer group
	 * @return Shopware\Models\Customer\Group
	 */
	public function getCustomerGroup()
	{
		$key = PlentymarketsConfig::getInstance()->get('DefaultCustomerGroupKey');

		return Shopware()->Models()->getRepository(
			'Shopware\Models\Customer\Group'
		)->findOneBy(array('key' => $key));
	}

}