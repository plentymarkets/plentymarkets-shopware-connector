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
 * @copyright  Copyright (c) 2013, plentymarkets GmbH (http://www.plentymarkets.com)
 * @author     Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */

require_once PY_SOAP . 'Models/PlentySoapObject/AddItemsBaseItemBase.php';
require_once PY_SOAP . 'Models/PlentySoapObject/Integer.php';
require_once PY_SOAP . 'Models/PlentySoapObject/ItemAttributeValueSet.php';
require_once PY_SOAP . 'Models/PlentySoapObject/ItemAvailability.php';
require_once PY_SOAP . 'Models/PlentySoapObject/ItemCategory.php';
require_once PY_SOAP . 'Models/PlentySoapObject/ItemFreeTextFields.php';
require_once PY_SOAP . 'Models/PlentySoapObject/ItemOthers.php';
require_once PY_SOAP . 'Models/PlentySoapObject/ItemPriceSet.php';
require_once PY_SOAP . 'Models/PlentySoapObject/ItemProperty.php';
require_once PY_SOAP . 'Models/PlentySoapObject/ItemStock.php';
require_once PY_SOAP . 'Models/PlentySoapObject/ItemSupplier.php';
require_once PY_SOAP . 'Models/PlentySoapObject/ItemTexts.php';
require_once PY_SOAP . 'Models/PlentySoapObject/String.php';
require_once PY_SOAP . 'Models/PlentySoapRequest/AddItemsBase.php';
require_once PY_SOAP . 'Models/PlentySoapObject/FileBase64Encoded.php';
require_once PY_SOAP . 'Models/PlentySoapObject/ItemImage.php';
require_once PY_SOAP . 'Models/PlentySoapRequest/AddItemsImage.php';
require_once PY_SOAP . 'Models/PlentySoapObject/AddPropertyToItem.php';
require_once PY_SOAP . 'Models/PlentySoapRequest/AddPropertyToItem.php';
require_once PY_SOAP . 'Models/PlentySoapObject/AddItemAttributeVariationList.php';
require_once PY_SOAP . 'Models/PlentySoapObject/AttributeVariantion.php';
require_once PY_SOAP . 'Models/PlentySoapObject/Integer.php';
require_once PY_SOAP . 'Models/PlentySoapRequest/AddItemAttributeValueSets.php';
require_once PY_SOAP . 'Models/PlentySoapObject/SetAttributeValueSetsDetails.php';
require_once PY_SOAP . 'Models/PlentySoapRequest/SetAttributeValueSetsDetails.php';
require_once PY_COMPONENTS . 'Utils/PlentymarketsUtils.php';
require_once PY_COMPONENTS . 'Mapping/PlentymarketsMappingController.php';

/**
 * PlentymarketsExportEntityItem provides the actual items export funcionality. Like the other export 
 * entities this class is called in PlentymarketsExportController. It is important to deliver the correct
 * article model to the constructor method of this class, which is \Shopware\Models\Article\Article.
 * The data export takes place based on plentymarkets SOAP-calls.
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsExportEntityItem
{

	/**
	 *
	 * @var \Shopware\Models\Article\Article
	 */
	protected $SHOPWARE_Article;

	/**
	 *
	 * @var integer
	 */
	protected $PLENTY_itemID;

	/**
	 *
	 * @var integer
	 */
	protected $PLENTY_priceID;

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
	 * @return boolean
	 */
	protected function exportItemBase()
	{
		$Item = $this->SHOPWARE_Article;

		try
		{
			// If there is a plenty id for this shopware id,
			// the item has already been exported to plentymarkets
			PlentymarketsMappingController::getItemByShopwareID($Item->getId());

			// Exit
			return false;
		}
		catch (PlentymarketsMappingExceptionNotExistant $E)
		{
		}

		$Item instanceof \Shopware\Models\Article\Article;

		$Request_AddItemsBase = new PlentySoapRequest_AddItemsBase();
		$Request_AddItemsBase->BaseItems = array();

		$Object_AddItemsBaseItemBase = new PlentySoapObject_AddItemsBaseItemBase();
		$ItemDetails = $Item->getMainDetail();

		// Release date
		$ReleaseDate = $ItemDetails->getReleaseDate();
		if ($ReleaseDate instanceof \DateTime)
		{
			$Object_AddItemsBaseItemBase->Published = $ReleaseDate->getTimestamp();
		}

		$Object_AddItemsBaseItemBase->ExternalItemID = PlentymarketsUtils::getExternalItemID($Item->getId());
		$Object_AddItemsBaseItemBase->ProducerID = PlentymarketsMappingController::getProducerByShopwareID($Item->getSupplier()->getId());
		$Object_AddItemsBaseItemBase->EAN1 = $ItemDetails->getEan();
		$Object_AddItemsBaseItemBase->VATInternalID = PlentymarketsMappingController::getVatByShopwareID($Item->getTax()->getId());
		$Object_AddItemsBaseItemBase->ItemNo = $ItemDetails->getNumber();

		//
		$Object_AddItemsBaseItemBase->Availability = $this->getObjectAvailabiliy();
		$Object_AddItemsBaseItemBase->Texts = $this->getObjectTexts();
		$Object_AddItemsBaseItemBase->FreeTextFields = $this->getObjectFreeTextFields();
		$Object_AddItemsBaseItemBase->PriceSet = $this->getObjectPriceSet();

		//
		$Object_AddItemsBaseItemBase->Categories = array();

		foreach ($Item->getCategories() as $Category)
		{
			$Category instanceof Shopware\Models\Category\Category;
			try
			{
				$categoryPath = PlentymarketsMappingController::getCategoryByShopwareID($Category->getId());
			}
			catch (PlentymarketsMappingExceptionNotExistant $E)
			{
				PlentymarketsLogger::getInstance()->error('Export:Item', 'ItemId ' . $Item->getId() . ': Skipping category with id ' . $Category->getId());
				continue;
			}

			$Object_ItemCategory = new PlentySoapObject_ItemCategory();
			$Object_ItemCategory->ItemCategoryPath = $categoryPath; // string
			$Object_AddItemsBaseItemBase->Categories[] = $Object_ItemCategory;
		}

		$Object_AddItemsBaseItemBase->StoreIDs = array();
		$Object_Integer = new PlentySoapObject_Integer();
		$Object_Integer->intValue = PlentymarketsConfig::getInstance()->getStoreID(); // int
		$Object_AddItemsBaseItemBase->StoreIDs[] = $Object_Integer;

		$Request_AddItemsBase->BaseItems[] = $Object_AddItemsBaseItemBase;

		$Response_AddItemsBase = PlentymarketsSoapClient::getInstance()->AddItemsBase($Request_AddItemsBase);

		$ResponseMessage = $Response_AddItemsBase->ResponseMessages->item[0];

		if ($ResponseMessage->Code != 100)
		{
			throw new Exception('ItemBase error');
		}

		foreach ($ResponseMessage->SuccessMessages->item as $SubMessage)
		{
			if ($SubMessage->Key == 'ItemID')
			{
				$this->PLENTY_itemID = (integer) $SubMessage->Value;
			}
			else if ($SubMessage->Key == 'PriceID')
			{
				$this->PLENTY_priceID = (integer) $SubMessage->Value;
			}
		}

		if ($this->PLENTY_itemID && $this->PLENTY_priceID)
		{
			PlentymarketsLogger::getInstance()->message('Export:Item', 'ItemId ' . $Item->getId() . ': Item created (ItemId: ' . $this->PLENTY_itemID . ', PriceId: ' . $this->PLENTY_priceID . ')');
			PlentymarketsMappingController::addItem($Item->getId(), $this->PLENTY_itemID);
		}
		else
		{
			PlentymarketsLogger::getInstance()->error('Export:Item', 'ItemId ' . $Item->getId() . ': Item could not be exported');
			throw new Exception('Did not recieve item ID and price ID');
		}

		return true;
	}

	/**
	 */
	protected function exportImages()
	{
		foreach ($this->SHOPWARE_Article->getImages() as $Image)
		{
			$Image instanceof Shopware\Models\Article\Image;

			$ImageMedia = $Image->getMedia();
			if (is_null($ImageMedia))
			{
				PlentymarketsLogger::getInstance()->error('Export:Item:Image', 'ItemId ' . $this->SHOPWARE_Article->getId() . ': Skipping image with id ' . $Image->getId() . ' because there is no media associated');
				continue;
			}
			$ImageMedia instanceof Shopware\Models\Media\Media;

			try
			{
				$fullpath = Shopware()->DocPath() . $ImageMedia->getPath();
			}
			catch (Exception $E)
			{
				PlentymarketsLogger::getInstance()->error('Export:Item:Image', 'ItemId ' . $this->SHOPWARE_Article->getId() . ': Skipping image with id ' . $Image->getId());
				PlentymarketsLogger::getInstance()->error('Export:Item:Image', $E->getMessage());
				continue;
			}

			$Request_AddItemsImage = new PlentySoapRequest_AddItemsImage();

			$Object_ItemImage = new PlentySoapObject_ItemImage();
			$Object_ItemImage->Availability = null; // i

			$Object_FileBase64Encoded = new PlentySoapObject_FileBase64Encoded();
			$Object_FileBase64Encoded->FileData = base64_encode(file_get_contents($fullpath)); // base64Binary
			$Object_FileBase64Encoded->FileEnding = $Image->getExtension(); // string
			$Object_FileBase64Encoded->FileName = $Image->getPath(); // string
			$Object_ItemImage->ImageData = $Object_FileBase64Encoded;
			$Object_ItemImage->Position = $Image->getPosition();

			$Request_AddItemsImage->Image = $Object_ItemImage;
			$Request_AddItemsImage->ItemID = $this->PLENTY_itemID;

			// Do the request
			$Response_AddItemsImage = PlentymarketsSoapClient::getInstance()->AddItemsImage($Request_AddItemsImage);
		}
	}

	/**
	 *
	 * @return PlentySoapObject_ItemAvailability
	 */
	protected function getObjectAvailabiliy()
	{
		//
		$Object_ItemAvailability = new PlentySoapObject_ItemAvailability();
		$Object_ItemAvailability->AvailableUntil = null; // int
		$Object_ItemAvailability->Inactive = (integer) !$this->SHOPWARE_Article->getActive(); // int
		$Object_ItemAvailability->IntervalSalesOrderQuantity = $this->SHOPWARE_Article->getMainDetail()->getPurchaseSteps(); // int
		$Object_ItemAvailability->MaximumSalesOrderQuantity = $this->SHOPWARE_Article->getMainDetail()->getMaxPurchase();
		$Object_ItemAvailability->MinimumSalesOrderQuantity = $this->SHOPWARE_Article->getMainDetail()->getMinPurchase();
		$Object_ItemAvailability->WebAPI = 1; // int
		$Object_ItemAvailability->Webshop = 1; // int

		return $Object_ItemAvailability;
	}

	/**
	 *
	 * @return PlentySoapObject_ItemTexts
	 */
	protected function getObjectTexts()
	{
		//
		$Object_ItemTexts = new PlentySoapObject_ItemTexts();
		$Object_ItemTexts->Keywords = $this->SHOPWARE_Article->getKeywords(); // string
		$Object_ItemTexts->Lang = 'de'; // string
		$Object_ItemTexts->LongDescription = $this->SHOPWARE_Article->getDescriptionLong(); // string
		$Object_ItemTexts->Name = $this->SHOPWARE_Article->getName(); // string
		$Object_ItemTexts->ShortDescription = $this->SHOPWARE_Article->getDescription(); // string

		return $Object_ItemTexts;
	}

	/**
	 *
	 * @return PlentySoapObject_ItemFreeTextFields
	 */
	protected function getObjectFreeTextFields()
	{
		//
		$MainDetailAttribute = $this->SHOPWARE_Article->getMainDetail()->getAttribute();

		//
		$Object_ItemFreeTextFields = new PlentySoapObject_ItemFreeTextFields();

		if (!is_null($MainDetailAttribute))
		{
			$Object_ItemFreeTextFields->Free1 = $MainDetailAttribute->getAttr1(); // string
			$Object_ItemFreeTextFields->Free2 = $MainDetailAttribute->getAttr2(); // string
			$Object_ItemFreeTextFields->Free3 = $MainDetailAttribute->getAttr3(); // string
			$Object_ItemFreeTextFields->Free4 = $MainDetailAttribute->getAttr4(); // string
			$Object_ItemFreeTextFields->Free5 = $MainDetailAttribute->getAttr5(); // string
			$Object_ItemFreeTextFields->Free6 = $MainDetailAttribute->getAttr6(); // string
			$Object_ItemFreeTextFields->Free7 = $MainDetailAttribute->getAttr7(); // string
			$Object_ItemFreeTextFields->Free8 = $MainDetailAttribute->getAttr8(); // string
			$Object_ItemFreeTextFields->Free9 = $MainDetailAttribute->getAttr9(); // string
			$Object_ItemFreeTextFields->Free10 = $MainDetailAttribute->getAttr10(); // string
			$Object_ItemFreeTextFields->Free11 = $MainDetailAttribute->getAttr11(); // string
			$Object_ItemFreeTextFields->Free12 = $MainDetailAttribute->getAttr12(); // string
			$Object_ItemFreeTextFields->Free13 = $MainDetailAttribute->getAttr13(); // string
			$Object_ItemFreeTextFields->Free14 = $MainDetailAttribute->getAttr14(); // string
			$Object_ItemFreeTextFields->Free15 = $MainDetailAttribute->getAttr15(); // string
			$Object_ItemFreeTextFields->Free16 = $MainDetailAttribute->getAttr16(); // string
			$Object_ItemFreeTextFields->Free17 = $MainDetailAttribute->getAttr17(); // string
			$Object_ItemFreeTextFields->Free18 = $MainDetailAttribute->getAttr18(); // string
			$Object_ItemFreeTextFields->Free19 = $MainDetailAttribute->getAttr19(); // string
			$Object_ItemFreeTextFields->Free20 = $MainDetailAttribute->getAttr20(); // string
		}

		return $Object_ItemFreeTextFields;
	}

	/**
	 *
	 * @return PlentySoapObject_ItemPriceSet
	 */
	protected function getObjectPriceSet()
	{
		//
		$MainDetail = $this->SHOPWARE_Article->getMainDetail();
		$Unit = $MainDetail->getUnit();
		$Tax = $this->SHOPWARE_Article->getTax();

		//
		$Object_ItemPriceSet = new PlentySoapObject_ItemPriceSet();
		$Object_ItemPriceSet->HeightInMM = $MainDetail->getHeight() * 100; // int
		$Object_ItemPriceSet->LengthInMM = $MainDetail->getLen() * 100;
		$Object_ItemPriceSet->Lot = $MainDetail->getPurchaseUnit(); // float
		$Object_ItemPriceSet->PackagingUnit = $MainDetail->getReferenceUnit();
		$Object_ItemPriceSet->PurchasePriceNet = null; // float
		$Object_ItemPriceSet->TypeOfPackage = null; // int
		$Object_ItemPriceSet->Unit1 = $MainDetail->getPackUnit();
		$Object_ItemPriceSet->WeightInGramm = $MainDetail->getWeight() * 1000;
		$Object_ItemPriceSet->WidthInMM = $MainDetail->getWidth() * 100; // int

		if ($Unit instanceof \Shopware\Models\Article\Unit)
		{
			$Object_ItemPriceSet->Unit = PlentymarketsMappingController::getMeasureUnitByShopwareID($Unit->getId()); // string
		}

		$prices = array();
		$ItemPrice = Shopware()->Models()->getRepository('Shopware\Models\Article\Price');
		foreach ($ItemPrice->findBy(array(
			'customerGroupKey' => PlentymarketsConfig::getInstance()->getDefaultCustomerGroupKey(),
			'articleDetailsId' => $MainDetail->getId()
		)) as $ItemPrice)
		{
			$ItemPrice instanceof Shopware\Models\Article\Price;

			$price = array();
			$price['to'] = $ItemPrice->getTo();
			$price['price'] = $ItemPrice->getPrice() * ($Tax->getTax() + 100) / 100;
			$price['rrp'] = $ItemPrice->getPseudoPrice() * ($Tax->getTax() + 100) / 100;
			$price['pp'] = $ItemPrice->getBasePrice();

			$prices[$ItemPrice->getFrom()] = $price;
		}

		//
		ksort($prices);

		//
		$n = 0;
		foreach ($prices as $from => $p)
		{
			// The base price
			if ($from == 1)
			{
				$Object_ItemPriceSet->RRP = $p['rrp'];
				$Object_ItemPriceSet->Price = $p['price'];
				$Object_ItemPriceSet->PurchasePriceNet = $p['pp'];
			}

			// Set PriceX values
			else
			{
				$priceIndex = 'Price' . ($n + 5);
				$Object_ItemPriceSet->$priceIndex = $p['price'];

				$rebateLevel = 'RebateLevelPrice' . ($n + 5);
				$Object_ItemPriceSet->$rebateLevel = $from;
			}
			++$n;
		}

		return $Object_ItemPriceSet;
	}

	/**
	 */
	public function export()
	{
		if (!$this->exportItemBase())
		{
			return false;
		}
		$this->exportImages();
		$this->exportVariants();
		$this->exportProperties();

		return true;
	}

	/**
	 */
	protected function exportProperties()
	{
		$Request_AddPropertyToItem = new PlentySoapRequest_AddPropertyToItem();
		$Request_AddPropertyToItem->PropertyToItemList = array();

		// max 50

		$PropertyGroup = $this->SHOPWARE_Article->getPropertyGroup();

		if (!$PropertyGroup instanceof \Shopware\Models\Property\Group)
		{
			return;
		}
		foreach ($this->SHOPWARE_Article->getPropertyValues() as $PropertyValue)
		{

			$PropertyValue instanceof Shopware\Models\Property\Value;

			$PropertyOption = $PropertyValue->getOption();

			$PLENTY_propertyID = PlentymarketsMappingController::getPropertyByShopwareID($PropertyGroup->getId() . ';' . $PropertyOption->getId());

			$Object_AddPropertyToItem = new PlentySoapObject_AddPropertyToItem();
			$Object_AddPropertyToItem->ItemId = $this->PLENTY_itemID; // int
			$Object_AddPropertyToItem->Lang = 'de'; // string
			$Object_AddPropertyToItem->PropertyId = $PLENTY_propertyID; // int
			$Object_AddPropertyToItem->PropertyItemSelectionValue = null; // int
			$Object_AddPropertyToItem->PropertyItemValue = $PropertyValue->getValue(); // string
			$Request_AddPropertyToItem->PropertyToItemList[] = $Object_AddPropertyToItem;
		}
		$Response_AddPropertyToItem = PlentymarketsSoapClient::getInstance()->AddPropertyToItem($Request_AddPropertyToItem);
	}

	protected function exportVariants()
	{

		// Verknüpfung mit den Attribut(-werten)
		$ConfiguratorSet = $this->SHOPWARE_Article->getConfiguratorSet();
		if (!$ConfiguratorSet instanceof Shopware\Models\Article\Configurator\Set)
		{
			return;
		}

		$Request_AddItemAttributeValueSets = new PlentySoapRequest_AddItemAttributeValueSets();
		$Request_AddItemAttributeValueSets->ItemID = $this->PLENTY_itemID; // int
		$Request_AddItemAttributeValueSets->ActivateVariations = array();
		$Request_AddItemAttributeValueSets->AddAttributeValueSets = array();

		// Attribut-Werte zuordnen
		foreach ($ConfiguratorSet->getOptions() as $ConfiguratorOption)
		{
			$ConfiguratorOption instanceof Shopware\Models\Article\Configurator\Option;

			$PLENTY_attributeID = PlentymarketsMappingController::getAttributeGroupByShopwareID($ConfiguratorOption->getGroup()->getId());
			$PLENTY_attributeValueID = PlentymarketsMappingController::getAttributeOptionByShopwareID($ConfiguratorOption->getId());

			$Object_AttributeVariantion = new PlentySoapObject_AttributeVariantion();
			$Object_AttributeVariantion->AttributeID = $PLENTY_attributeID; // int
			$Object_AttributeVariantion->AttributeValueID = $PLENTY_attributeValueID; // int
			$Request_AddItemAttributeValueSets->ActivateVariations[] = $Object_AttributeVariantion;
		}

		$cacheAttributeValueSets = array();

		$Details = $this->SHOPWARE_Article->getDetails();

		// Varianten erstellen
		foreach ($Details as $ItemVariation)
		{
			$ItemVariation instanceof Shopware\Models\Article\Detail;

			$cacheAttributeValueSets[$ItemVariation->getId()] = array();

			$Object_AddItemAttributeVariationList = new PlentySoapObject_AddItemAttributeVariationList();

			foreach ($ItemVariation->getConfiguratorOptions() as $ConfiguratorOption)
			{
				$ConfiguratorOption instanceof Shopware\Models\Article\Configurator\Option;

				$PLENTY_attributeValueID = PlentymarketsMappingController::getAttributeOptionByShopwareID($ConfiguratorOption->getId());

				$cacheAttributeValueSets[$ItemVariation->getId()][] = $PLENTY_attributeValueID;

				$Object_Integer = new PlentySoapObject_Integer();
				$Object_Integer->intValue = $PLENTY_attributeValueID;
				$Object_AddItemAttributeVariationList->AttributeValueIDs[] = $Object_Integer;
			}

			$Request_AddItemAttributeValueSets->AddAttributeValueSets[] = $Object_AddItemAttributeVariationList;
		}

		$Response_AddItemAttributeValueSets = PlentymarketsSoapClient::getInstance()->AddItemAttributeValueSets($Request_AddItemAttributeValueSets);

		// Matching der Varianten
		foreach ($Response_AddItemAttributeValueSets->ResponseMessages->item as $ResponseMessage)
		{
			if ($ResponseMessage->IdentificationKey != 'AttributeValueIDs')
			{
				continue;
			}

			$PLENTY_attributeValueIDs = array_map('intval', explode(';', $ResponseMessage->IdentificationValue));
			$PLENTY_variantID = (integer) $ResponseMessage->SuccessMessages->item[0]->Value;

			foreach ($cacheAttributeValueSets as $SHOPWARE_variantID => $attributeValueIDs)
			{

				if (PlentymarketsUtils::arraysAreEqual($attributeValueIDs, $PLENTY_attributeValueIDs))
				{
					PlentymarketsMappingController::addItemVariant($SHOPWARE_variantID, sprintf('%s-%s-%s', $this->PLENTY_itemID, $this->PLENTY_priceID, $PLENTY_variantID));
					break;
				}
			}
		}

		//
		$Request_SetAttributeValueSetsDetails = new PlentySoapRequest_SetAttributeValueSetsDetails();
		$Request_SetAttributeValueSetsDetails->AttributeValueSetsDetails = array();

		// Varianten details
		foreach ($Details as $ItemVariation)
		{
			$ItemVariation instanceof Shopware\Models\Article\Detail;
			try
			{
				$sku = PlentymarketsMappingController::getItemVariantByShopwareID($ItemVariation->getId());
			}
			catch (PlentymarketsMappingExceptionNotExistant $E)
			{
				PlentymarketsLogger::getInstance()->error('Export:Item:Variant', 'ItemId ' . $this->SHOPWARE_Article->getId() . ': Skipping corrut variant with id ' . $ItemVariation->getId());
				continue;
			}

			$Object_SetAttributeValueSetsDetails = new PlentySoapObject_SetAttributeValueSetsDetails();
			// $Object_SetAttributeValueSetsDetails->ASIN = $ItemVariation->getActive(); // string
			$Object_SetAttributeValueSetsDetails->Availability = $ItemVariation->getActive(); // int
			$Object_SetAttributeValueSetsDetails->EAN1 = $ItemVariation->getEan(); // string
			$Object_SetAttributeValueSetsDetails->MaxStock = null; // float
			$Object_SetAttributeValueSetsDetails->PurchasePrice = null; // float
			$Object_SetAttributeValueSetsDetails->SKU = $sku;
			// $Object_SetAttributeValueSetsDetails->StockBuffer = $ItemVariation->get; // float

			$Object_SetAttributeValueSetsDetails->Variantnumber = $ItemVariation->getNumber(); // string
			$Request_SetAttributeValueSetsDetails->AttributeValueSetsDetails[] = $Object_SetAttributeValueSetsDetails;
		}

		// Do the request
		$Response_SetAttributeValueSetsDetails = PlentymarketsSoapClient::getInstance()->SetAttributeValueSetsDetails($Request_SetAttributeValueSetsDetails);
	}
}
