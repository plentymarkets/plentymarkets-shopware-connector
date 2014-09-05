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


/**
 * PlentymarketsImportEntityItem provides the actual item import functionality. Like the other import
 * entities this class is called in PlentymarketsImportController. It is important to deliver the correct PlentySoapObject_ItemBase
 * object to the constructor method of this class.
 * The data import takes place based on plentymarkets SOAP-calls.
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsImportEntityItem
{

	/**
	 *
	 * @var PlentySoapObject_ItemBase
	 */
	protected $ItemBase;

	/**
	 *
	 * @var Shopware\Models\Shop\Shop
	 */
	protected $Shop;

	/**
	 * plentymarkets store id
	 *
	 * @var integer
	 */
	protected $storeId;

	/**
	 * The main data
	 *
	 * @var array
	 */
	protected $data;

	/**
	 *
	 * @var array
	 */
	protected $details;

	/**
	 *
	 * @var array
	 */
	protected $variants = array();

	/**
	 *
	 * @var array
	 */
	protected $categories = array();

	/**
	 *
	 * @var Shopware\Components\Api\Resource\Article
	 */
	protected static $ArticleApi;

	/**
	 *
	 * @var Shopware\Components\Api\Resource\Variant
	 */
	protected static $VariantApi;

	/**
	 *
	 * @var Shopware\Components\Api\Resource\Category
	 */
	protected static $CategoryApi;

	/**
	 *
	 * @var Shopware\Models\Category\Repository
	 */
	protected static $CategoryRepository;

	/**
	 * Constructor method
	 *
	 * @param PlentySoapObject_ItemBase $ItemBase
	 * @param Shopware\Models\Shop\Shop $Shop
	 */
	public function __construct($ItemBase, Shopware\Models\Shop\Shop $Shop)
	{
		$this->ItemBase = $ItemBase;
		$this->Shop = $Shop;
		$this->storeId = PlentymarketsMappingController::getShopByShopwareID($Shop->getId());
	}

	/**
	 * Sets the base item's data – not the details'
	 */
	protected function setData()
	{
		$this->data = array(
			'name' => $this->ItemBase->Texts->Name,
			'description' => $this->ItemBase->Texts->ShortDescription,
			'descriptionLong' => $this->ItemBase->Texts->LongDescription,
			'keywords' => $this->ItemBase->Texts->Keywords,
			'highlight' => ($this->ItemBase->WebShopSpecial == 3),
			'lastStock' => ($this->ItemBase->Stock->Limitation == 1),
			'changed' => date('c', $this->ItemBase->LastUpdate),
			'availableTo' => null,
			'active' => $this->ItemBase->Availability->Inactive == 0 && $this->ItemBase->Availability->Webshop == 1,
			'taxId' => $this->getTaxId()
		);

		if ($this->ItemBase->Availability->AvailableUntil > 0)
		{
			$this->data['availableTo'] = date('c', $this->ItemBase->Availability->AvailableUntil);
		}

		try
		{
			$this->data['supplierId'] = PlentymarketsMappingController::getProducerByPlentyID($this->ItemBase->ProducerID);
		}
		catch (PlentymarketsMappingExceptionNotExistant $E)
		{
			if ($this->ItemBase->ProducerName)
			{
				$this->data['supplier'] = $this->ItemBase->ProducerName;
			}
			else
			{
				$this->data['supplierId'] = PlentymarketsConfig::getInstance()->getItemProducerID();
			}
		}
	}

	/**
	 * Set the base details
	 */
	protected function setDetails()
	{
		// Shipping time
		$shippingTime = PlentymarketsUtils::getShippingTimeByAvailabilityId($this->ItemBase->Availability->AvailabilityID);

		// Active
		$active = $this->ItemBase->Availability->Inactive == 0 && $this->ItemBase->Availability->Webshop == 1;

		$details = array(
			'active' => $active,
			'ean' => $this->ItemBase->EAN1,
			'minPurchase' => null,
			'purchaseSteps' => null,
			'maxPurchase' => null,
			'purchaseUnit' => null,
			'referenceUnit' => null,
			'shippingtime' => $shippingTime,
			'packUnit' => trim($this->ItemBase->PriceSet->Unit1),
			'releaseDate' => ($this->ItemBase->Published == 0 ? null : date('c', $this->ItemBase->Published)),
			'weight' => null,
			'width' => null,
			'len' => null,
			'height' => null,
			'attribute' => array(
				'attr1' => $this->ItemBase->FreeTextFields->Free1,
				'attr2' => $this->ItemBase->FreeTextFields->Free2,
				'attr3' => $this->ItemBase->FreeTextFields->Free3,
				'attr4' => $this->ItemBase->FreeTextFields->Free4,
				'attr5' => $this->ItemBase->FreeTextFields->Free5,
				'attr6' => $this->ItemBase->FreeTextFields->Free6,
				'attr7' => $this->ItemBase->FreeTextFields->Free7,
				'attr8' => $this->ItemBase->FreeTextFields->Free8,
				'attr9' => $this->ItemBase->FreeTextFields->Free9,
				'attr10' => $this->ItemBase->FreeTextFields->Free10,
				'attr11' => $this->ItemBase->FreeTextFields->Free11,
				'attr12' => $this->ItemBase->FreeTextFields->Free12,
				'attr13' => $this->ItemBase->FreeTextFields->Free13,
				'attr14' => $this->ItemBase->FreeTextFields->Free14,
				'attr15' => $this->ItemBase->FreeTextFields->Free15,
				'attr16' => $this->ItemBase->FreeTextFields->Free16,
				'attr17' => $this->ItemBase->FreeTextFields->Free17,
				'attr18' => $this->ItemBase->FreeTextFields->Free18,
				'attr19' => $this->ItemBase->FreeTextFields->Free19,
				'attr20' => $this->ItemBase->FreeTextFields->Free20
			)
		);

		if ($this->ItemBase->Availability->MinimumSalesOrderQuantity > 0)
		{
			$details['minPurchase'] = $this->ItemBase->Availability->MinimumSalesOrderQuantity;
		}

		if ($this->ItemBase->Availability->IntervalSalesOrderQuantity > 0)
		{
			$details['purchaseSteps'] = $this->ItemBase->Availability->IntervalSalesOrderQuantity;
		}

		if ($this->ItemBase->Availability->MaximumSalesOrderQuantity > 0)
		{
			$details['maxPurchase'] = $this->ItemBase->Availability->MaximumSalesOrderQuantity;
		}

		if ($this->ItemBase->PriceSet->Lot > 0)
		{
			$details['purchaseUnit'] = $this->ItemBase->PriceSet->Lot;
		}

		if ($this->ItemBase->PriceSet->PackagingUnit > 0)
		{
			$details['referenceUnit'] = $this->ItemBase->PriceSet->PackagingUnit;
		}

		if ($this->ItemBase->PriceSet->WeightInGramm > 0)
		{
			$details['weight'] = $this->ItemBase->PriceSet->WeightInGramm / 1000;
		}

		if ($this->ItemBase->PriceSet->WidthInMM > 0)
		{
			$details['width'] = $this->ItemBase->PriceSet->WidthInMM / 10;
		}

		if ($this->ItemBase->PriceSet->LengthInMM > 0)
		{
			$details['len'] = $this->ItemBase->PriceSet->LengthInMM / 10;
		}

		if ($this->ItemBase->PriceSet->HeightInMM > 0)
		{
			$details['height'] = $this->ItemBase->PriceSet->HeightInMM / 10;
		}

		if (strlen($this->ItemBase->PriceSet->Unit))
		{
			try
			{
				$details['unitId'] = PlentymarketsMappingController::getMeasureUnitByPlentyID($this->ItemBase->PriceSet->Unit);
			}
			catch (PlentymarketsMappingExceptionNotExistant $E)
			{
				$details['unitId'] = null;
			}
		}

		$this->details = $details;
	}

	/**
	 * Sets the variant details
	 */
	protected function setVariants()
	{
		// No variants
		if (is_null($this->ItemBase->AttributeValueSets))
		{
			return;
		}

		// Internal number cache
		$numbersUsed = array();

		$detailBase = $this->details + $this->data;
		unset($detailBase['id']);
		unset($detailBase['attribute']);

		/** @var PlentySoapObject_ItemAttributeValueSet $AttributeValueSet */
		foreach ($this->ItemBase->AttributeValueSets->item as $AttributeValueSet)
		{
			// Copy the base details
			$details = $detailBase;

			// SKU
			$sku = sprintf('%s-%s-%s', $this->ItemBase->ItemID, $AttributeValueSet->PriceID, $AttributeValueSet->AttributeValueSetID);

			// Strip whitespaces
			$number = trim($AttributeValueSet->ColliNo);

			try
			{
				// Set the details id
				$details['id'] = PlentymarketsMappingController::getItemVariantByPlentyID($sku);

				if (PlentymarketsConfig::getInstance()->getItemNumberImportActionID(IMPORT_ITEM_NUMBER) == IMPORT_ITEM_NUMBER)
				{

					// If this number does not belong to this item
					if (!PlentymarketsImportItemHelper::isNumberExistantVariant($number, $details['id']))
					{
						// and check if the number is valid
						if (!PlentymarketsImportItemHelper::isNumberValid($number))
						{
							throw new PlentymarketsImportItemNumberException('The item variation number »' . $number . '« of item »' . $this->data['name'] . '« with the id »' . $this->ItemBase->ItemID . '« is invalid', 3110);
						}

						// check if the number is available anyway
						if (PlentymarketsImportItemHelper::isNumberExistant($number))
						{
							throw new PlentymarketsImportItemNumberException('The item variation number »' . $number . '« of item »' . $this->data['name'] . '« with the id »' . $this->ItemBase->ItemID . '« is already in use', 3111);
						}

						// check if the number is in the internal cache
						if (isset($numbersUsed[$number]))
						{
							throw new PlentymarketsImportItemNumberException('The item variation number »' . $number . '« of item »' . $this->data['name'] . '« with the id »' . $this->ItemBase->ItemID . '« would be assigned twice', 3112);
						}

						// Use this number
						$details['number'] = $number;

						// Cache the number
						$numbersUsed[$number] = true;
					}
				}
			}
			catch (PlentymarketsMappingExceptionNotExistant $e)
			{
				// Numbers should be synced
				if (PlentymarketsConfig::getInstance()->getItemNumberImportActionID(IMPORT_ITEM_NUMBER) == IMPORT_ITEM_NUMBER)
				{
					// Nummer ist ungültig oder in Benutzung
					if (!PlentymarketsImportItemHelper::isNumberValid($number))
					{
						throw new PlentymarketsImportItemNumberException('The item variation number »' . $number . '« of item »' . $this->data['name'] . '« with the id »' . $this->ItemBase->ItemID . '« is invalid', 3110);
					}

					// check if the number is available
					if (PlentymarketsImportItemHelper::isNumberExistant($number))
					{
						throw new PlentymarketsImportItemNumberException('The item variation number »' . $number . '« of item »' . $this->data['name'] . '« with the id »' . $this->ItemBase->ItemID . '« is already in use', 3111);
					}

					// check if the number is in the internal cache
					if (isset($numbersUsed[$number]))
					{
						throw new PlentymarketsImportItemNumberException('The item variation number »' . $number . '« of item »' . $this->data['name'] . '« with the id »' . $this->ItemBase->ItemID . '« would be assigned twice', 3112);
					}

					// Use this number
					$details['number'] = $number;

					// Cache the number
					$numbersUsed[$number] = true;
				}

				else
				{
					// A new number is generated
					$details['number'] = PlentymarketsImportItemHelper::getItemNumber();
				}
			}

			$shippingTime = PlentymarketsUtils::getShippingTimeByAvailabilityId($AttributeValueSet->Availability);
			if ($shippingTime)
			{
				$details['shippingtime'] = $shippingTime;
			}

			$details['additionaltext'] = $AttributeValueSet->AttributeValueSetName;
			$details['ean'] = $AttributeValueSet->EAN;
			$details['X_plentySku'] = $sku;

			$this->variants[$AttributeValueSet->AttributeValueSetID] = $details;
		}
	}

	/**
	 * Sets the categories. Non-existing categories will be created immediately.
	 */
	protected function setCategories()
	{
		// No categories
		if (is_null($this->ItemBase->Categories))
		{
			return;
		}

		if (is_null(self::$CategoryApi))
		{
			self::$CategoryApi = Shopware\Components\Api\Manager::getResource('Category');
		}

		if (is_null(self::$CategoryRepository))
		{
			self::$CategoryRepository = Shopware()->Models()->getRepository('Shopware\Models\Category\Category');
		}

		// Categories
		/** @var PlentySoapObject_ItemCategory $Category */
		foreach ($this->ItemBase->Categories->item as $Category)
		{
			// FIX: corrupt category within plenty
			if ((integer) $Category->ItemCategoryID <= 0 || empty($Category->ItemCategoryPathNames))
			{
				continue;
			}

			try
			{
				$categoryId = PlentymarketsMappingEntityCategory::getCategoryByPlentyID($Category->ItemCategoryID, $this->storeId);
				$this->categories[] = array(
					'id' => $categoryId
				);
			}

			// Category does not yet exist
			catch (PlentymarketsMappingExceptionNotExistant $E)
			{
				$importEntityItemCategoryTree = new PlentymarketsImportEntityItemCategoryTree($Category, $this->storeId);
				$categoryId = $importEntityItemCategoryTree->import();

				// Only create a mapping and connect the category to the item,
				// of nothing went wrong during creation
				if ($categoryId)
				{
					$this->categories[] = array(
						'id' => $categoryId
					);
				}
			}
		}
	}

	/**
	 * Set the item's properties
	 */
	protected function setProperties()
	{
		// No properties
		if (is_null($this->ItemBase->ItemProperties))
		{
			return;
		}

		$groups = array();

		/** @var PlentySoapObject_ItemProperty $ItemProperty */
		foreach ($this->ItemBase->ItemProperties->item as $ItemProperty)
		{
			if (is_null($ItemProperty->PropertyGroupID))
			{
				PlentymarketsLogger::getInstance()->error('Sync:Item', 'The property »' . $ItemProperty->PropertyName . '« will not be assigned to the item »' . $this->data['name'] . '« with the id »' . $this->ItemBase->ItemID . '« (not assigned to any group)', 3410);
			}

			else if (!$ItemProperty->ShowOnItemPageInWebshop)
			{
				PlentymarketsLogger::getInstance()->error('Sync:Item', 'The property »' . $ItemProperty->PropertyName . '« will not be assigned to the item »' . $this->data['name'] . '« with the id »' . $this->ItemBase->ItemID . '« (may not be shown on item page)', 3420);
			}

			else
			{
				$groups[$ItemProperty->PropertyGroupID][] = $ItemProperty;
			}
		}

		if (empty($groups))
		{
			PlentymarketsLogger::getInstance()->error('Sync:Item', 'No property group will be assigned to the item »' . $this->data['name'] . '« with the id »' . $this->ItemBase->ItemID . '«', 3430);
			return;
		}

		$groupId = -1;
		$numberOfValuesMax = 0;

		foreach ($groups as $groupIdx => $values)
		{
			if (count($values) > $numberOfValuesMax)
			{
				$groupId = $groupIdx;
				$numberOfValuesMax = count($values);
			}
		}

		// Check for filterId
		try
		{
			$filterGroupId = PlentymarketsMappingController::getPropertyGroupByPlentyID($groupId);
		}
		catch (PlentymarketsMappingExceptionNotExistant $E)
		{
			// Create the group
			$GroupAdded = Shopware\Components\Api\Manager::getResource('PropertyGroup')->create(array(
				'name' => $ItemProperty->PropertyGroupFrontendName
			));

			// Get the new id
			$filterGroupId = $GroupAdded->getId();

			// Write the mapping and the log
			PlentymarketsMappingController::addPropertyGroup($filterGroupId, $groupId);
			PlentymarketsLogger::getInstance()->message('Sync:Item', 'The property group »' . $ItemProperty->PropertyGroupFrontendName . '« has been created');
		}

		// Load the group consistently
		/** @var Shopware\Models\Property\Group $Group */
		$Group = Shopware()->Models()->find('Shopware\Models\Property\Group', $filterGroupId);

		if (!$Group)
		{
			throw new PlentymarketsImportItemException('The property group with the id »' . $filterGroupId . '« could not be loaded', 3441);
		}

		// Update the data of this item
		$this->data['filterGroupId'] = $filterGroupId;
		$this->data['propertyValues'] = array();

		// Properties
		/** @var PlentySoapObject_ItemProperty $ItemProperty */
		foreach ($groups[$groupId] as $ItemProperty)
		{
			// Mapping GroupId;ValueId -> ValueId
			try
			{
				$property = PlentymarketsMappingController::getPropertyByPlentyID($ItemProperty->PropertyID);
				$propertyParts = explode(';', $property);
				$optionId = $propertyParts[1];
			}
			catch (PlentymarketsMappingExceptionNotExistant $E)
			{

				$Option = new Shopware\Models\Property\Option();
				$Option->fromArray(array(
					'name' => $ItemProperty->PropertyName,
					'filterable' => 1
				));

				// Persist the objects
				Shopware()->Models()->persist($Group);
				Shopware()->Models()->persist($Option);

				// Add the option to the group
				$Group->addOption($Option);

				try
				{
					// And flush everything
					Shopware()->Models()->flush();
					PlentymarketsLogger::getInstance()->message('Sync:Item', 'The property »' . $ItemProperty->PropertyName . '« has been created and added to the group »' . $Group->getName() . '«');
				}
				catch (Exception $E)
				{
					throw new PlentymarketsImportItemException('The property »' . $ItemProperty->PropertyName . '« could not be created ('. $E->getMessage() .')', 3440);
				}

				//
				$optionId = $Option->getId();

				// Save the mapping
				PlentymarketsMappingController::addProperty($filterGroupId . ';' . $optionId, $ItemProperty->PropertyID);
			}

			// Shopware cannot handle empty values
			if (!empty($ItemProperty->PropertyValue))
			{
				$this->data['propertyValues'][] = array(
					'option' => array(
						'id' => $optionId
					),
					'value' => $ItemProperty->PropertyValue
				);
			}
		}
	}

	/**
	 * Just update the categories
	 *
	 * This method is called, if the item has already been updated though another store
	 */
	public function importCategories()
	{
		$this->setCategories();

		$ArticleResource = self::getArticleApi();

		$SHOPWARE_itemID = PlentymarketsMappingController::getItemByPlentyID($this->ItemBase->ItemID);
		$article = $ArticleResource->getOne($SHOPWARE_itemID);

		$data = array(
			'categories' => $article['categories']
		);

		foreach ($this->categories as $category)
		{
			if (isset($article['categories'][$category['id']]))
			{
				continue;
			}
			$data['categories'][$category['id']] = $category;
		}

		if (count($data['categories']) != count($article['categories']))
		{
			$ArticleResource->update($SHOPWARE_itemID, $data);
		}
	}

	/**
	 * Handles the whole import
	 */
	public function import()
	{
		$this->setData();
		$this->setDetails();
		$this->setVariants();
		$this->setProperties();

		$data = $this->data;
		$data['mainDetail'] = $this->details;

		$mainDetailId = -1;

		$ArticleResource = self::getArticleApi();
		$VariantResource = self::getVariantApi();

		try
		{
			// If a mappings exists, it's a regular item
			$SHOPWARE_itemID = PlentymarketsMappingController::getItemByPlentyID($this->ItemBase->ItemID);


			// Should the categories be synchronized?
			if (PlentymarketsConfig::getInstance()->getItemCategorySyncActionID(IMPORT_ITEM_CATEGORY_SYNC) == IMPORT_ITEM_CATEGORY_SYNC)
			{
				$this->setCategories();
				$data['categories'] = $this->categories;
			}

			// Should the number be synchronized?
			// This does only matter if there are no variants
			if (PlentymarketsConfig::getInstance()->getItemNumberImportActionID(IMPORT_ITEM_NUMBER) == IMPORT_ITEM_NUMBER && !count($this->variants))
			{
				// strip whitespaces
				$number = trim($this->ItemBase->ItemNo);

				// If this number does not belong to this item
				if (!PlentymarketsImportItemHelper::isNumberExistantItem($number, $SHOPWARE_itemID))
				{
					// and check if the number is valid
					if (!PlentymarketsImportItemHelper::isNumberValid($number))
					{
						throw new PlentymarketsImportItemNumberException('The item number »' . $number . '« of item »' . $this->data['name'] . '« with the id »' . $this->ItemBase->ItemID . '« is invalid', 3120);
					}

					// check if the number is available anyway
					if (PlentymarketsImportItemHelper::isNumberExistant($number))
					{
						throw new PlentymarketsImportItemNumberException('The item number »' . $number . '« of item »' . $this->data['name'] . '« with the id »' . $this->ItemBase->ItemID . '« is already in use', 3121);
					}

					// then update it
					$data['mainDetail']['number'] = $number;
				}
			}

			// Update the item
			$Article = $ArticleResource->update($SHOPWARE_itemID, $data);

			// Log
			PlentymarketsLogger::getInstance()->message('Sync:Item', sprintf('The item »%s« with the number »%s« has been updated', $data['name'], $Article->getMainDetail()->getNumber()));

			// Remember the main detail's id (to set the prices)
			$mainDetailId = $Article->getMainDetail()->getId();

			// Variants that will be commited to the API
			$variants = array();

			$update = array();
			$number2sku = array();
			$keep = array(
				'numbers' => array(),
				'ids' => array()
			);

			// Es gibt varianten
			if (count($this->variants))
			{
				//
				$VariantController = new PlentymarketsImportItemVariantController($this->ItemBase);

				// Counter
				$numberOfVariantsUpdated = 0;
				$numberOfVariantsCreated = 0;
				$numberOfVariantsDeleted = 0;

				foreach ($this->variants as $variantId => $variant)
				{
					// Directly add the prices
					$PlentymarketsImportEntityItemPrice = new PlentymarketsImportEntityItemPrice(
						$this->ItemBase->PriceSet, $VariantController->getMarkupByVariantId($variantId)
					);
					$variant['prices'] = $PlentymarketsImportEntityItemPrice->getPrices();

					// If the variant has an id, it is already created and mapped soo we just keep it
					if (array_key_exists('id', $variant))
					{
						++$numberOfVariantsUpdated;
						$keep['ids'][] = $variant['id'];
					}

					// otherwise the variant needs to be created
					else
					{
						++$numberOfVariantsCreated;

						$variant['configuratorOptions'] = $VariantController->getOptionsByVariantId($variantId);

						$keep['numbers'][] = $variant['number'];

						// Internal mapping of the variant number to some plenty information
						$number2sku[$variant['number']] = $variant['X_plentySku'];
					}

					$variants[] = $variant;
				}

				// The configurator set has to be adapted
				$update['configuratorSet'] = array(
					'groups' => $VariantController->getGroups()
				);

				// Delete all variants
				if ($numberOfVariantsUpdated == 0)
				{
					$Article = $ArticleResource->update($SHOPWARE_itemID, array(
						'configuratorSet' => array(
							'groups' => array()
						),
						'variations' => array()
					));
				}

				$update['variants'] = $variants;

				// Check if the main detail will be deleted
				if (!in_array($mainDetailId, $keep['ids']))
				{
					// Promote the first variante to be the main detail
					$update['variants'][0]['isMain'] = true;
					$deleteMainVariant = true;
				}
				else
				{
					$deleteMainVariant = false;
				}

				$ArticleResource->update($SHOPWARE_itemID, $update);

				// Check if the main detail will be deleted
				if ($deleteMainVariant)
				{
					// If the main detail is not needed anymore, delete it right away
					// Otherwise it will be a dead data record. The main details are not
					// returned from the API->getOne call. Only the "real" main detail.
					Shopware()->Models()->remove(
						Shopware()->Models()->find(
							'Shopware\Models\Article\Detail', $mainDetailId
						)
					);
					Shopware()->Models()->flush();

					PlentymarketsMappingController::deleteItemVariantByShopwareID($mainDetailId);

					++$numberOfVariantsDeleted;
				}

				$article = $ArticleResource->getOne($SHOPWARE_itemID);

				// Add the main detail
				$article['details'][] = $article['mainDetail'];

				// Mapping for the variants
				foreach ($article['details'] as $detail)
				{
					// If the variant is not needed anymore - delete it
					if (!in_array($detail['number'], $keep['numbers']) && !in_array($detail['id'], $keep['ids']))
					{
						++$numberOfVariantsDeleted;
						$VariantResource->delete($detail['id']);
						PlentymarketsMappingController::deleteItemVariantByShopwareID($detail['id']);
					}

					// If the variant was just created
					else if (isset($number2sku[$detail['number']]))
					{
						// Add the mapping
						PlentymarketsMappingController::addItemVariant($detail['id'], $number2sku[$detail['number']]);
					}
				}

				$VariantController->map($article);

				$messages = array();

				// Log
				if ($numberOfVariantsUpdated == 1)
				{
					$messages[] = '1 variant has been updated';
				}
				else if ($numberOfVariantsUpdated > 1)
				{
					$messages[] = $numberOfVariantsUpdated . ' variants have been updated';
				}
				if ($numberOfVariantsCreated == 1)
				{
					$messages[] = '1 variant has been created';
				}
				else if ($numberOfVariantsCreated > 1)
				{
					$messages[] = $numberOfVariantsCreated . ' variants have been created';
				}
				if ($numberOfVariantsDeleted == 1)
				{
					$messages[] = '1 variant has been deleted';
				}
				else if ($numberOfVariantsDeleted > 1)
				{
					$messages[] = $numberOfVariantsDeleted . ' variants have been deleted';
				}

				if ($messages)
				{
					PlentymarketsLogger::getInstance()->message('Sync:Item', implode(', ', $messages));
				}
			}
			else
			{
				// Preise eines Normalen Artikels aktualisieren
				$PlentymarketsImportEntityItemPrice = new PlentymarketsImportEntityItemPrice($this->ItemBase->PriceSet);
				$PlentymarketsImportEntityItemPrice->update($SHOPWARE_itemID);
			}

			// Bilder
			if (PlentymarketsConfig::getInstance()->getItemImageSyncActionID(IMPORT_ITEM_IMAGE_SYNC) == IMPORT_ITEM_IMAGE_SYNC)
			{
				$PlentymarketsImportEntityItemImage = new PlentymarketsImportEntityItemImage($this->ItemBase->ItemID, $SHOPWARE_itemID);
				$PlentymarketsImportEntityItemImage->image();
			}
		}

		// Artikel muss importiert werden / Es ist kein Basisartikel
		catch (PlentymarketsMappingExceptionNotExistant $E)
		{
			// Set the categories no matter what
			$this->setCategories();
			$data['categories'] = $this->categories;

			// Regular item
			if (!count($this->variants))
			{
				// Numbers should be synced
				if (PlentymarketsConfig::getInstance()->getItemNumberImportActionID(IMPORT_ITEM_NUMBER) == IMPORT_ITEM_NUMBER)
				{
					// strip whitespaces
					$number = trim($this->ItemBase->ItemNo);

					// Nummer ist ungültig oder in Benutzung
					if (!PlentymarketsImportItemHelper::isNumberValid($number))
					{
						throw new PlentymarketsImportItemNumberException('The item number »' . $number . '« of item »' . $this->data['name'] . '« with the id »' . $this->ItemBase->ItemID . '« is invalid', 3120);
					}

					if (PlentymarketsImportItemHelper::isNumberExistant($number))
					{
						throw new PlentymarketsImportItemNumberException('The item number »' . $number . '« of item »' . $this->data['name'] . '« with the id »' . $this->ItemBase->ItemID . '« is already in use', 3121);
					}

					// Use this number
					$data['mainDetail']['number'] = $number;
				}

				else
				{
					// A new number is generated
					$data['mainDetail']['number'] = PlentymarketsImportItemHelper::getItemNumber();
				}

				// Create
				$Article = $ArticleResource->create($data);

				//
				$SHOPWARE_itemID = $Article->getId();

				// Log
				PlentymarketsLogger::getInstance()->message('Sync:Item', 'The item »' . $this->data['name'] . '« has been created with the number »' . $data['mainDetail']['number'] . '«');

				// Mapping speichern
				PlentymarketsMappingController::addItem($Article->getId(), $this->ItemBase->ItemID);

				// Stock stack
				PlentymarketsImportItemStockStack::getInstance()->add($this->ItemBase->ItemID);

				// Media

				// Preise
				$PlentymarketsImportEntityItemPrice = new PlentymarketsImportEntityItemPrice($this->ItemBase->PriceSet);
				$PlentymarketsImportEntityItemPrice->update($Article->getId());
			}

			else
			{
				// Set the id of the first variant
				$mainVariant = array_shift(array_values($this->variants));
				$data['mainDetail']['number'] = $mainVariant['number'];

				// Anlegen
				$Article = $ArticleResource->create($data);
				PlentymarketsLogger::getInstance()->message('Sync:Item', 'The variant base item »' . $this->data['name'] . '« has been created created with the number »' . $data['mainDetail']['number'] . '«');

				//
				$SHOPWARE_itemID = $Article->getId();

				// Mapping speichern
				PlentymarketsMappingController::addItem($Article->getId(), $this->ItemBase->ItemID);

				$VariantController = new PlentymarketsImportItemVariantController($this->ItemBase);

				//
				$number2sku = array();

				//
				foreach ($this->variants as $variantId => &$variant)
				{
					$variant['configuratorOptions'] = $VariantController->getOptionsByVariantId($variantId);

					// Prices
					$PlentymarketsImportEntityItemPrice = new PlentymarketsImportEntityItemPrice($this->ItemBase->PriceSet, $VariantController->getMarkupByVariantId($variantId));
					$variant['prices'] = $PlentymarketsImportEntityItemPrice->getPrices();

					$number2sku[$variant['number']] = $variant['X_plentySku'];
				}

				// Varianten
				$id = $Article->getId();

				$updateArticle = array(

					'configuratorSet' => array(
						'groups' => $VariantController->getGroups()
					),

					'variants' => array_values($this->variants)
				);

				PlentymarketsLogger::getInstance()->message('Sync:Item:Variant', 'Starting to create variants for the item »' . $this->data['name'] . '« with the number »' . $data['mainDetail']['number'] . '«');

				$Article = $ArticleResource->update($id, $updateArticle);

				/**@var Shopware\Models\Article\Detail $detail */
				foreach ($Article->getDetails() as $detail)
				{
					// Save mapping and add the variant to the stock stack
					$sku = $number2sku[$detail->getNumber()];
					PlentymarketsMappingController::addItemVariant($detail->getId(), $sku);
					PlentymarketsImportItemStockStack::getInstance()->add($sku);
				}

				$VariantController->map($ArticleResource->getOne($id));

				PlentymarketsLogger::getInstance()->message('Sync:Item:Variant', 'Variants created successfully');
			}

			// Bilder
			$PlentymarketsImportEntityItemImage = new PlentymarketsImportEntityItemImage($this->ItemBase->ItemID, $SHOPWARE_itemID);
			$PlentymarketsImportEntityItemImage->image();
		}

		// Rebuild category tree
		if (count($this->categories))
		{
			/** @var \Shopware\Components\Model\CategoryDenormalization $component */
			$component = Shopware()->CategoryDenormalization();
			$component->removeArticleAssignmentments($SHOPWARE_itemID);
			foreach ($this->categories as $category)
			{
				$component->addAssignment($SHOPWARE_itemID, $category['id']);
			}
		}

		// Der Hersteller ist neu angelegt worden
		if ($Article instanceof Shopware\Models\Article\Article && array_key_exists('supplier', $this->data))
		{
			PlentymarketsLogger::getInstance()->message('Sync:Item', 'The producer »' . $Article->getSupplier()->getName() . '« has been created');
			PlentymarketsMappingController::addProducer($Article->getSupplier()->getId(), $this->ItemBase->ProducerID);
		}
	}

	/**
	 * Returns the shopware tax id
	 *
	 * @throws PlentymarketsImportItemException
	 * @return integer
	 */
	protected function getTaxId()
	{
		try
		{
			$taxID = PlentymarketsMappingController::getVatByPlentyID($this->ItemBase->VATInternalID);
		}
		catch (PlentymarketsMappingExceptionNotExistant $E)
		{
			throw new PlentymarketsImportItemException('The item »'. $this->ItemBase->Texts->Name .'« with the id »'. $this->ItemBase->ItemID .'« could not be imported (no valid vat/tax)', 3030);
		}

		return $taxID;
	}

	/**
	 * Returns the Atricle resource
	 *
	 * @return \Shopware\Components\Api\Resource\Article
	 */
	protected static function getArticleApi()
	{
		if (is_null(self::$ArticleApi))
		{
			self::$ArticleApi = Shopware\Components\Api\Manager::getResource('Article');
		}

		return self::$ArticleApi;
	}

	/**
	 * Returns the Variant resource
	 *
	 * @return \Shopware\Components\Api\Resource\Variant
	 */
	protected static function getVariantApi()
	{
		if (is_null(self::$VariantApi))
		{
			self::$VariantApi = Shopware\Components\Api\Manager::getResource('Variant');
		}

		return self::$VariantApi;
	}
}
