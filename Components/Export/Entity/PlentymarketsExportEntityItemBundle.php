<?php
/**
 * plentymarkets shopware connector
 * Copyright © 2013 plentymarkets GmbH
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License, supplemented by an additional
 * permission, and of our proprietary license can be found
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "plentymarkets" is a registered trademark of plentymarkets GmbH.
 * "shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, titles and interests in the
 * above trademarks remain entirely with the trademark owners.
 *
 * @copyright Copyright (c) 2013, plentymarkets GmbH (http://www.plentymarkets.com)
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */

require_once PY_SOAP . 'Models/PlentySoapObject/AddItemsBaseItemBase.php';
require_once PY_SOAP . 'Models/PlentySoapObject/Integer.php';
require_once PY_SOAP . 'Models/PlentySoapObject/ItemAvailability.php';
require_once PY_SOAP . 'Models/PlentySoapObject/ItemCategory.php';
require_once PY_SOAP . 'Models/PlentySoapObject/ItemPriceSet.php';
require_once PY_SOAP . 'Models/PlentySoapObject/ItemTexts.php';
require_once PY_SOAP . 'Models/PlentySoapRequest/AddItemsBase.php';
require_once PY_SOAP . 'Models/PlentySoapObject/Integer.php';
require_once PY_SOAP . 'Models/PlentySoapObject/AddBundle.php';
require_once PY_SOAP . 'Models/PlentySoapObject/AddBundleItem.php';
require_once PY_SOAP . 'Models/PlentySoapRequest/AddItemsToBundle.php';

/**
 * Exports an item bundle
 */
class PlentymarketsExportEntityItemBundle
{

	/**
	 * @var Shopware\CustomModels\Bundle\Bundle
	 */
	protected $SHOPWARE_bundle;

	/**
	 * @var array
	 */
	protected $PLENTY_bundleSkuList = array();

	/**
	 * @var integer
	 */
	protected $PLENTY_bundleHeadId;

	/**
	 * I am the constructor
	 *
	 * @param \Shopware\CustomModels\Bundle\Bundle $bundle
	 */
	public function __construct(Shopware\CustomModels\Bundle\Bundle $bundle)
	{
		$this->SHOPWARE_bundle = $bundle;
	}

	/**
	 * Runs the actual export of the item bundle
	 */
	public function export()
	{
		$this->index();
		$this->exportHead();
		$this->exportItems();
	}

	/**
	 * Builds an index of all items inside the bundle
	 * and checks if all of these items are exported to plentymarkets
	 *
	 * @throws PlentymarketsExportEntityException
	 */
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
					throw new PlentymarketsExportEntityException('The item bundle with the number »' . $this->SHOPWARE_bundle->getNumber() . '« can not be exported because not all of the items are available in plentymarkets.', 2220);
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

	/**
	 * Exports the item bundle head item
	 *
	 * @throws PlentymarketsExportException
	 */
	protected function exportHead()
	{
		// The shopware item on which the bundle is based on
		$shopwareBundleHead = $this->SHOPWARE_bundle->getArticle();
		$shopwareBundleHeadIsVariant = !is_null($shopwareBundleHead->getConfiguratorSet());

		// If the bundle head is a variant, the bundle can't be exported
		// since that feature is not provided by plentymarkets
		if ($shopwareBundleHeadIsVariant)
		{
			throw new PlentymarketsExportException('The item bundle with the number »' . $this->SHOPWARE_bundle->getNumber() . '« can not be exported because the master item is a variant.', 2230);
		}

		if ($this->SHOPWARE_bundle->getDiscountType() != 'abs')
		{
			throw new PlentymarketsExportException('The item bundle with the number »' . $this->SHOPWARE_bundle->getNumber() . '« can not be exported because the discount type is not supported.', 2240);
		}

		if ($this->SHOPWARE_bundle->getType() != 1)
		{
			throw new PlentymarketsExportException('The item bundle with the number »' . $this->SHOPWARE_bundle->getNumber() . '« can not be exported because the bundle type is not supported.', 2250);
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
			$tax = $this->SHOPWARE_bundle->getArticle()->getTax()->getTax();
			$priceNet = $price->getPrice();
			$price = $priceNet + ($priceNet / 100 * $tax);
			$Object_ItemPriceSet->Price = $price;
			$Object_ItemPriceSet->VAT = $tax;
		}
		else
		{
			// If there is no price, we have to set one anyway.
			// Otherwise the re-import will crash
			$Object_ItemPriceSet->Price = 1;
		}
		$Object_AddItemsBaseItemBase->PriceSet = $Object_ItemPriceSet;
		$Object_AddItemsBaseItemBase->VATInternalID = PlentymarketsMappingController::getVatByShopwareID($this->SHOPWARE_bundle->getArticle()->getTax()->getId());

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
			throw new PlentymarketsExportException('The item bundle with the number »' . $this->SHOPWARE_bundle->getNumber() . '« could not be exported', 2210);
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
			PlentymarketsLogger::getInstance()->message('Export:Initial:Item:Bundle', 'The item bundle with the number »' . $this->SHOPWARE_bundle->getNumber() . '« has been created with the id »' . $this->PLENTY_bundleHeadId . '«.');
			PlentymarketsMappingController::addItemBundle($this->SHOPWARE_bundle->getId(), $this->PLENTY_bundleHeadId);
		}
		else
		{
			throw new PlentymarketsExportException('The item bundle with the number »' . $this->SHOPWARE_bundle->getNumber() . '« could not be exported', 2210);
		}
	}

	/**
	 * Exports the items of the bundle
	 *
	 * todo: max 50
	 * todo: analyse response
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
		PlentymarketsLogger::getInstance()->message('Export:Initial:Item:Bundle', $numberAdded . ' items have been added to the item bundle with the number »' . $this->SHOPWARE_bundle->getNumber() . '«.');
	}
}
