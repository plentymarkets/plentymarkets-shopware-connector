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
 * The PlentymarketsImportItemVariantController class is used in the entity class PlentymarketsImportEntityItem
 * to process the variants data. It is important to deliver the correct object PlentySoapObject_ItemBase
 * to the constructor method.
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsImportItemVariantController
{
    /**
     * @var array
     */
    public static $mapping = [
        'group' => [],
        'option' => [],
    ];

    /**
     * @var PlentySoapObject_ItemBase
     */
    protected $ItemBase;

    /**
     * @var array
     */
    protected $groupName2plentyId = [];

    /**
     * @var array
     */
    protected $groupId2optionName2plentyId = [];

    /**
     * @var array
     */
    protected $groups = [];

    /**
     * @var array
     */
    protected $variants = [];

    /**
     * @var array
     */
    protected $variant2markup = [];

    /**
     * @var array
     */
    protected $configuratorOptions = [];

    /**
     * @var array
     */
    protected $valueId2markup = [];

    /**
     * @var array
     */
    protected $referencePrices;

    /**
     * @var
     */
    protected $purchasePrices;

    /**
     * Constructor method
     *
     * @param PlentySoapObject_ItemBase $ItemBase
     */
    public function __construct($ItemBase)
    {
        $this->referencePrices = [];
        $this->purchasePrices = [];

        $this->ItemBase = $ItemBase;
	    
	$priceForMarkup = $this->getItemPrice($this->ItemBase->PriceSet);

        foreach ($this->ItemBase->ItemAttributeMarkup->item as $ItemAttributeMarkup) {
            // May be percentage or flat rate surcharge
            $this->valueId2markup[$ItemAttributeMarkup->ValueID] = (float) $ItemAttributeMarkup->Markup;
        }

        $Request_GetAttributeValueSets = new PlentySoapRequest_GetAttributeValueSets();

        $valueIds = [];

        $chunks = array_chunk($this->ItemBase->AttributeValueSets->item, 50);

        foreach ($chunks as $chunk) {
            $Request_GetAttributeValueSets->AttributeValueSets = [];

            /**
             * Attribute Value Sets abfragen
             *
             * @var PlentySoapObject_ItemAttributeValueSet $AttributeValueSet
             */
            foreach ($chunk as $AttributeValueSet) {
                $this->variants[$AttributeValueSet->AttributeValueSetID] = [];

                $RequestObject_GetAttributeValueSets = new PlentySoapRequestObject_GetAttributeValueSets();
                $RequestObject_GetAttributeValueSets->AttributeValueSetID = $AttributeValueSet->AttributeValueSetID;
                $RequestObject_GetAttributeValueSets->Lang = 'de';
                $Request_GetAttributeValueSets->AttributeValueSets[] = $RequestObject_GetAttributeValueSets;

                // Reference Price (UVP)
                $this->referencePrices[$AttributeValueSet->AttributeValueSetID] = $AttributeValueSet->UVP;

                // Purchase Price
                $this->purchasePrices[$AttributeValueSet->AttributeValueSetID] = $AttributeValueSet->PurchasePrice;
            }

            /**
             * @var PlentySoapResponse_GetAttributeValueSets $Response_GetAttributeValueSets
             */
            $Response_GetAttributeValueSets = PlentymarketsSoapClient::getInstance()->GetAttributeValueSets($Request_GetAttributeValueSets);

            /**
             * @var PlentySoapObject_AttributeValueSet $AttributeValueSet
             * @var PlentySoapObject_Attribute $Attribute
             */
            foreach ($Response_GetAttributeValueSets->AttributeValueSets->item as $AttributeValueSet) {
                $this->variant2markup[$AttributeValueSet->AttributeValueSetID] = 0.0;

                foreach ($AttributeValueSet->Attribute->item as $Attribute) {
                    if (!array_key_exists($Attribute->AttributeFrontendName, $this->groups)) {
                        try {
                            $attributeId = PlentymarketsMappingController::getAttributeGroupByPlentyID($Attribute->AttributeID);
                            $group = [
                                'id' => $attributeId,
                                'options' => [],
                            ];
                        } catch (Exception $e) {
                            $group = [
                                'name' => $Attribute->AttributeFrontendName,
                                'options' => [],
                            ];
                        }

                        $this->groups[$Attribute->AttributeFrontendName] = $group;

                        $this->groupId2optionName2plentyId[$Attribute->AttributeID] = [];
                        $this->groupName2plentyId[$Attribute->AttributeFrontendName] = $Attribute->AttributeID;
                    }

                    $this->configuratorOptions[$AttributeValueSet->AttributeValueSetID][] = [
                        'group' => $Attribute->AttributeFrontendName,
                        'option' => $Attribute->AttributeValue->ValueFrontendName,
                    ];

                    if (!in_array($Attribute->AttributeValue->ValueID, $valueIds)) {
                        try {
                            $valueId = PlentymarketsMappingController::getAttributeOptionByPlentyID($Attribute->AttributeValue->ValueID);
                            $option = [
                                'id' => $valueId,
                            ];
                        } catch (Exception $e) {
                            $option = [
                                'name' => $Attribute->AttributeValue->ValueFrontendName,
                            ];
                        }

                        $this->groups[$Attribute->AttributeFrontendName]['options'][] = $option;
                        $this->groupId2optionName2plentyId[$Attribute->AttributeID][$Attribute->AttributeValue->ValueFrontendName] = $Attribute->AttributeValue->ValueID;
                        $valueIds[] = $Attribute->AttributeValue->ValueID;
                    }

                    if ($this->valueId2markup[$Attribute->AttributeValue->ValueID]) {
                        $markup = $this->valueId2markup[$Attribute->AttributeValue->ValueID];
                    } else {
                        $markup = (float) $Attribute->AttributeValue->Markup;
                    }

                    if ($markup) {
                        if ($Attribute->MarkupPercental == 1) {
                            $markup = $priceForMarkup / 100 * $markup;
                        }

                        $this->variant2markup[$AttributeValueSet->AttributeValueSetID] += $markup;
                    }
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
        $details = $article['details'];
        $details[] = $article['mainDetail'];

        foreach ($details as $detail) {
            foreach ($detail['configuratorOptions'] as $option) {
                try {
                    // Mapping for the Group -> plentymarkets Attribute
                    if (!isset(self::$mapping['group'][$option['groupId']])) {
                        $plentyGroupId = PlentymarketsMappingController::getAttributeGroupByShopwareID($option['groupId']);
                    } else {
                        $plentyGroupId = self::$mapping['group'][$option['groupId']];
                    }
                } catch (PlentymarketsMappingExceptionNotExistant $E) {
                    // Name auslesen
                    $group = Shopware()->Db()->fetchRow('
						SELECT name FROM s_article_configurator_groups WHERE id = ?
					', [
                        $option['groupId'],
                    ]);

                    $plentyGroupId = $this->groupName2plentyId[$group['name']];

                    PlentymarketsMappingController::addAttributeGroup($option['groupId'], $plentyGroupId);
                }

                try {
                    // Mapping for the Group -> plentymarkets Attribute
                    if (!isset(self::$mapping['option'][$option['id']])) {
                        $plentyOptionId = PlentymarketsMappingController::getAttributeOptionByShopwareID($option['id']);
                    } else {
                        $plentyOptionId = self::$mapping['option'][$option['id']];
                    }
                } catch (PlentymarketsMappingExceptionNotExistant $E) {
                    $plentyOptionId = $this->groupId2optionName2plentyId[$plentyGroupId][$option['name']];

                    PlentymarketsMappingController::addAttributeOption($option['id'], $plentyOptionId);
                }

                self::$mapping['group'][$option['groupId']] = $plentyGroupId;
                self::$mapping['option'][$option['id']] = $plentyOptionId;
            }
        }
    }

    /**
     * Returns an array of configurator options for the use with the shopware REST API
     *
     * @param int $variantId
     *
     * @return array
     */
    public function getOptionsByVariantId($variantId)
    {
        return $this->configuratorOptions[$variantId];
    }

    /**
     * Return the markup for a variant
     *
     * @param int $variantId
     *
     * @return array
     */
    public function getMarkupByVariantId($variantId)
    {
        return $this->variant2markup[$variantId];
    }

    /**
     * @param $variantId
     *
     * @return float
     */
    public function getReferencePriceByVaraintId($variantId)
    {
        return $this->referencePrices[$variantId];
    }

    /**
     * @param $variantId
     *
     * @return float
     */
    public function getPurchasePriceByVariantId($variantId)
    {
        return $this->purchasePrices[$variantId];
    }

    /**
     * Returns an array of configurator groups for the use with the shopware REST API
     *
     * @return array
     */
    public function getGroups()
    {
        return array_values($this->groups);
    }
	
    /**
     * Returns the item price
     *
     * @return double
     */
    protected function getItemPrice($ItemPrices)
    {
        $usePrice = PlentymarketsConfig::getInstance()->getItemPriceImportActionID(IMPORT_ITEM_PRICE);

        if($usePrice != 'Price')
        {
            if(!empty($ItemPrices->{$usePrice}))
            {
                return $ItemPrices->{$usePrice};
            }
        }

        return $ItemPrices->Price;
    }
}
