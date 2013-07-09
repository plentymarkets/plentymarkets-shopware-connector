<?php

/**
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsVariantController
{

	/**
	 *
	 * @var PlentySoapObject_ItemBase
	 */
	protected $ItemBase;

	/**
	 *
	 * @var array
	 */
	protected $groupName2plentyId = array();

	/**
	 *
	 * @var array
	 */
	protected $groupId2optionName2plentyId = array();

	/**
	 *
	 * @var array
	 */
	protected $groups = array();

	/**
	 *
	 * @var array
	 */
	protected $variants = array();

	/**
	 *
	 * @var array
	 */
	protected $variant2markup = array();

	/**
	 *
	 * @var array
	 */
	protected $configuratorOptions = array();

	/**
	 *
	 * @var array
	 */
	protected $valueId2markup = array();

	/**
	 *
	 * @var array
	 */
	static $mapping = array(
		'group' => array(),
		'option' => array()
	);

	/**
	 *
	 * @param PlentySoapObject_ItemBase $ItemBase
	 */
	public function __construct($ItemBase)
	{
		$this->ItemBase = $ItemBase;

		foreach ($this->ItemBase->ItemAttributeMarkup->item as $ItemAttributeMarkup)
		{
			$this->valueId2Markup[$ItemAttributeMarkup->ValueID] = (float) $ItemAttributeMarkup->Markup;
		}

		//
		$Request_GetAttributeValueSets = new PlentySoapRequest_GetAttributeValueSets();
		$Request_GetAttributeValueSets->AttributeValueSets = array();

		// Attribute Value Sets abfragen
		foreach ($this->ItemBase->AttributeValueSets->item as $AttributeValueSet)
		{
			//
			$this->variants[$AttributeValueSet->AttributeValueSetID] = array();

			//
			$RequestObject_GetAttributeValueSets = new PlentySoapRequestObject_GetAttributeValueSets();
			$RequestObject_GetAttributeValueSets->AttributeValueSetID = $AttributeValueSet->AttributeValueSetID;
			$RequestObject_GetAttributeValueSets->Lang = 'de';
			$Request_GetAttributeValueSets->AttributeValueSets[] = $RequestObject_GetAttributeValueSets;
		}

		$valueIds = array();
		$Response_GetAttributeValueSets = PlentymarketsSoapClient::getInstance()->GetAttributeValueSets($Request_GetAttributeValueSets);
		foreach ($Response_GetAttributeValueSets->AttributeValueSets->item as $AttributeValueSet)
		{
			$AttributeValueSet instanceof PlentySoapObject_AttributeValueSet;

			$this->variant2markup[$AttributeValueSet->AttributeValueSetID] = 0;

			$options = array();

			foreach ($AttributeValueSet->Attribute->item as $Attribute)
			{
				$Attribute instanceof PlentySoapObject_Attribute;

				//
				if (!array_key_exists($Attribute->AttributeFrontendName, $this->groups))
				{
					$this->groups[$Attribute->AttributeFrontendName] = array(
						'name' => $Attribute->AttributeFrontendName,
						'options' => array()
					);

					$options[$Attribute->AttributeFrontendName] = array();

					$this->groupId2optionName2plentyId[$Attribute->AttributeID] = array();
					$this->groupName2plentyId[$Attribute->AttributeFrontendName] = $Attribute->AttributeID;
				}

				//
				$this->configuratorOptions[$AttributeValueSet->AttributeValueSetID][] = array(
					'group' => $Attribute->AttributeFrontendName,
					'option' => $Attribute->AttributeValue->ValueFrontendName
				);

				//
				if (!in_array($Attribute->AttributeValue->ValueID, $valueIds))
				{
					$this->groups[$Attribute->AttributeFrontendName]['options'][] = array(
						'name' => $Attribute->AttributeValue->ValueFrontendName
					);
					$this->groupId2optionName2plentyId[$Attribute->AttributeID][$Attribute->AttributeValue->ValueFrontendName] = $Attribute->AttributeValue->ValueID;
					$valueIds[] = $Attribute->AttributeValue->ValueID;
				}

				if ($this->valueId2Markup[$Attribute->AttributeValue->ValueID])
				{
					$this->variant2markup[$AttributeValueSet->AttributeValueSetID] += $this->valueId2Markup[$Attribute->AttributeValue->ValueID];
				}
				else
				{
					$this->variant2markup[$AttributeValueSet->AttributeValueSetID] += (float) $Attribute->AttributeValue->Markup;
				}
			}
		}
	}

	/**
	 * Generates "reverse mapping"
	 *
	 * @param array $article
	 */
	public function map($article)
	{
		foreach ($article['details'] as $detail)
		{
			foreach ($detail['configuratorOptions'] as $option)
			{
				try
				{
					// Mapping for the Group -> plentymarkets Attribute
					if (!isset(self::$mapping['group'][$option['groupId']]))
					{
						$plentyGroupId = PlentymarketsMappingController::getAttributeGroupByShopwareID($option['groupId']);
					}
					else
					{
						$plentyGroupId = self::$mapping['group'][$option['groupId']];
					}
				}
				catch (PlentymarketsMappingExceptionNotExistant $E)
				{
					// Name auslesen
					$group = Shopware()->Db()->fetchRow('
						SELECT name FROM s_article_configurator_groups WHERE id = ?
					', array(
						$option['groupId']
					));

					//
					$plentyGroupId = $this->groupName2plentyId[$group['name']];

					//
					PlentymarketsMappingController::addAttributeGroup($option['groupId'], $plentyGroupId);
				}

				try
				{
					// Mapping for the Group -> plentymarkets Attribute
					if (!isset(self::$mapping['option'][$option['id']]))
					{
						$plentyOptionId = PlentymarketsMappingController::getAttributeOptionByShopwareID($option['id']);
					}
					else
					{
						$plentyOptionId = self::$mapping['option'][$option['id']];
					}
				}
				catch (PlentymarketsMappingExceptionNotExistant $E)
				{
					//
					$plentyOptionId = $this->groupId2optionName2plentyId[$plentyGroupId][$option['name']];

					//
					PlentymarketsMappingController::addAttributeOption($option['id'], $plentyOptionId);

					//
				}

				self::$mapping['group'][$option['groupId']] = $plentyGroupId;
				self::$mapping['option'][$option['id']] = $plentyOptionId;
			}
		}
	}

	/**
	 *
	 * @param integer $variantId
	 * @return array
	 */
	public function getOptionsByVariantId($variantId)
	{
		return $this->configuratorOptions[$variantId];
	}

	/**
	 *
	 * @param integer $variantId
	 * @return array
	 */
	public function getMarkupByVariantId($variantId)
	{
		return $this->variant2markup[$variantId];
	}

	/**
	 *
	 * @return array
	 */
	public function getGroups()
	{
		return array_values($this->groups);
	}
}
