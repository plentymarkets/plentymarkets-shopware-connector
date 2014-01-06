<?php

/**
 * Created by IntelliJ IDEA.
 * User: dbaechtle
 * Date: 03.01.14
 * Time: 13:29
 */
class PlentymarketsExportEntityItemBundle
{

	protected $SHOPWARE_bundle;

	protected $PLENTY_bundleSkuList = array();

	protected $PLENTY_bundleHeadId;

	public function __construct(Shopware\CustomModels\Bundle\Bundle $bundle)
	{
		$this->SHOPWARE_bundle = $bundle;
	}

	public function export()
	{
		$this->index();
		$this->exportHead();
		$this->exportItems();
	}

	protected function index()
	{
		/** @var $bundleArticle Shopware\CustomModels\Bundle\Article */
		foreach ($this->SHOPWARE_bundle->getArticles() as $bundleArticle)
		{
			try
			{
				// Variant
				$sku = PlentymarketsMappingController::getItemVariantByShopwareID($bundleArticle->getArticleDetail()->getId());
			}
			catch (PlentymarketsMappingExceptionNotExistant $E)
			{
				try
				{
					// Base item
					$bundleItemId = PlentymarketsMappingController::getItemByShopwareID($bundleArticle->getArticleDetail()->getArticle()->getId());
					$sku = sprintf('%d-0', $bundleItemId);
				}
				catch (PlentymarketsMappingExceptionNotExistant $E)
				{
					throw new PlentymarketsExportEntityException('The item bundle xxx cannot be exported, (items missing in plenty)');
				}
			}
			$quantity = $bundleArticle->getQuantity();
			if (isset($this->PLENTY_bundleSkuList[$sku]))
			{
				$this->PLENTY_bundleSkuList[$sku] += $quantity;
			}
			else
			{
				$this->PLENTY_bundleSkuList[$sku] = $quantity;
			}
		}
	}

	protected function exportHead()
	{
		// The shopware item on which the bundle is based on
		$shopwareBundleHead = $this->SHOPWARE_bundle->getArticle();
		$shopwareBundleHeadIsVariant = !is_null($shopwareBundleHead->getConfiguratorSet());

		// If the bundle head is a variant, the bundle can't be exported
		// since that feature is not provided by plentymarkets
		if ($shopwareBundleHeadIsVariant)
		{
			throw new Exception('not supported');
		}

		if ($this->SHOPWARE_bundle->getDiscountType() != 'abs')
		{
			throw new Exception('only absulute allowed');
		}

		// The shopware bundle head needs to be added as a plenty-bundle-item
		// The bundle head in plentymarkets is a "special" item
		$bundleItemId = PlentymarketsMappingController::getItemByShopwareID($shopwareBundleHead->getId());
		$sku = sprintf('%d-0', $bundleItemId);

		// If this item is also a bundle item in shopware,
		// we need to increase the quantity of it
		if (isset($this->PLENTY_bundleSkuList[$sku]))
		{
			$this->PLENTY_bundleSkuList[$sku] += 1;
		}
		else
		{
			$this->PLENTY_bundleSkuList[$sku] = 1;
		}

		// Create the bundle head
		$Request_AddItemsBase = new PlentySoapRequest_AddItemsBase();

		$Request_AddItemsBase->BaseItems = array();
		$Object_AddItemsBaseItemBase = new PlentySoapObject_AddItemsBaseItemBase();

		$Object_ItemAvailability = new PlentySoapObject_ItemAvailability();

		$validTo = $this->SHOPWARE_bundle->getValidTo();
		if ($validTo instanceof DateTime)
		{
			$Object_ItemAvailability->AvailableUntil = $this->SHOPWARE_bundle->getValidTo()->getTimestamp();
		}

		$Object_ItemAvailability->WebAPI = 1;
		$Object_ItemAvailability->Inactive = (integer) $this->SHOPWARE_bundle->getActive();
		$Object_ItemAvailability->Webshop = (integer) $this->SHOPWARE_bundle->getActive();
		$Object_AddItemsBaseItemBase->Availability = $Object_ItemAvailability;

		$storeIds = array();
		$Object_AddItemsBaseItemBase->Categories = array();
		$Object_AddItemsBaseItemBase->StoreIDs = array();

		foreach ($shopwareBundleHead->getCategories() as $category)
		{
			/** @var $category Shopware\Models\Category\Category */
			try
			{
				$categoryPath = PlentymarketsMappingController::getCategoryByShopwareID($category->getId());
			}
			catch (PlentymarketsMappingExceptionNotExistant $E)
			{
				continue;
			}

			$Object_ItemCategory = new PlentySoapObject_ItemCategory();
			$Object_ItemCategory->ItemCategoryPath = $categoryPath; // string
			$Object_AddItemsBaseItemBase->Categories[] = $Object_ItemCategory;

			// Get the store for this category
			$rootId = PlentymarketsUtils::getRootIdByCategory($category);
			$shops = PlentymarketsUtils::getShopIdByCategoryRootId($rootId);

			foreach ($shops as $shopId)
			{
				try
				{
					$storeId = PlentymarketsMappingController::getShopByShopwareID($shopId);
				}
				catch (PlentymarketsMappingExceptionNotExistant $E)
				{
					continue;
				}

				if (!isset($storeIds[$storeId]))
				{
					// Activate the item for this store
					$Object_Integer = new PlentySoapObject_Integer();
					$Object_Integer->intValue = $storeId;
					$Object_AddItemsBaseItemBase->StoreIDs[] = $Object_Integer;

					// Cache
					$storeIds[$storeId] = true;
				}
			}
		}

		$Object_AddItemsBaseItemBase->ExternalItemID = 'Swag/Bundle/' . $this->SHOPWARE_bundle->getId(); // string
		$Object_AddItemsBaseItemBase->ItemNo = $this->SHOPWARE_bundle->getNumber(); // string

		$Object_ItemPriceSet = new PlentySoapObject_ItemPriceSet();
		$defaultCustomerGroupKey = PlentymarketsConfig::getInstance()->get('DefaultCustomerGroupKey');
		$price = null;
		$isPriceFound = false;
		foreach ($this->SHOPWARE_bundle->getPrices() as $price)
		{
			/** @var $price Shopware\CustomModels\Bundle\Price */
			if ($price->getCustomerGroup()->getKey() == $defaultCustomerGroupKey)
			{
				$isPriceFound = true;
				break;
			}
		}

		if ($isPriceFound && $price instanceof Shopware\CustomModels\Bundle\Price)
		{
			$Object_ItemPriceSet->Price = $price->getPrice(); // float
		}
		else
		{
			// If there is no price, we have to set one anyway.
			// Otherwise the re-import will crash
			$Object_ItemPriceSet->Price = 1;
		}
		$Object_AddItemsBaseItemBase->PriceSet = $Object_ItemPriceSet;

		$Object_AddItemsBaseItemBase->ProducerID = PlentymarketsMappingController::getProducerByShopwareID($shopwareBundleHead->getSupplier()->getId());; // int
		$Object_AddItemsBaseItemBase->Published = null; // int

		$Object_ItemTexts = new PlentySoapObject_ItemTexts();
		$Object_ItemTexts->Name = $this->SHOPWARE_bundle->getName(); // string
		$Object_AddItemsBaseItemBase->Texts = $Object_ItemTexts;

		$Request_AddItemsBase->BaseItems[] = $Object_AddItemsBaseItemBase;

		$Response_AddItemsBase = PlentymarketsSoapClient::getInstance()->AddItemsBase($Request_AddItemsBase);

		$ResponseMessage = $Response_AddItemsBase->ResponseMessages->item[0];

		if (!$Response_AddItemsBase->Success || $ResponseMessage->Code != 100)
		{
			throw new PlentymarketsExportException('The bundle head with the number »' . $this->SHOPWARE_bundle->getNumber() . '« could not be exported', 2800);
		}

		$PLENTY_priceID = null;
		foreach ($ResponseMessage->SuccessMessages->item as $SubMessage)
		{
			if ($SubMessage->Key == 'ItemID')
			{
				$this->PLENTY_bundleHeadId = (integer) $SubMessage->Value;
			}
			else if ($SubMessage->Key == 'PriceID')
			{
				$PLENTY_priceID = (integer) $SubMessage->Value;
			}
		}

		if ($this->PLENTY_bundleHeadId && $PLENTY_priceID)
		{
			PlentymarketsLogger::getInstance()->message('Export:Initial:Item:Bundle', 'The bundle head with the number »' . $this->SHOPWARE_bundle->getNumber() . '« has been created (ItemId: ' . $this->PLENTY_bundleHeadId . ', PriceId: ' . $PLENTY_priceID . ')');
			PlentymarketsMappingController::addItemBundle($this->SHOPWARE_bundle->getId(), $this->PLENTY_bundleHeadId);
		}
		else
		{
			throw new PlentymarketsExportException('The bundle head with the number »' . $this->SHOPWARE_bundle->getNumber() . '« could not be exported (no item ID and price ID respectively)', 2830);
		}
	}

	/**
	 * todo: max 50
	 * todo: response auswerten
	 */
	protected function exportItems()
	{
		$Request_AddItemsToBundle = new PlentySoapRequest_AddItemsToBundle();
		$Request_AddItemsToBundle->Bundles = array();

		$Object_AddBundle = new PlentySoapObject_AddBundle();
		$Object_AddBundle->BundleItems = array();

		foreach ($this->PLENTY_bundleSkuList as $sku => $quantity)
		{
			$Object_AddBundleItem = new PlentySoapObject_AddBundleItem();
			$Object_AddBundleItem->ItemSKU = $sku;
			$Object_AddBundleItem->Quantity = $quantity;
			$Object_AddBundle->BundleItems[] = $Object_AddBundleItem;
		}

		$Object_AddBundle->BundleSKU = $this->PLENTY_bundleHeadId; // string
		$Request_AddItemsToBundle->Bundles[] = $Object_AddBundle;

		PlentymarketsSoapClient::getInstance()->AddItemsToBundle($Request_AddItemsToBundle);

		$numberAdded = count($Object_AddBundle->BundleItems);
		PlentymarketsLogger::getInstance()->message('Export:Initial:Item:Bundle', $numberAdded . ' items have been added to the bundle with the number »' . $this->SHOPWARE_bundle->getNumber() . '«');
	}
} 