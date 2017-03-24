<?php
/**
 * plentymarkets shopware connector
 * Copyright © 2013 plentymarkets GmbH.
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
 * PlentymarketsExportControllerItemAttribute provides the actual items export functionality. Like the other export
 * entities this class is called in PlentymarketsExportController.
 * The data export takes place based on plentymarkets SOAP-calls.
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsExportControllerItemAttribute
{
    /**
     * @var array
     */
    protected $mappingShopwareID2PlentyID = [];

    /**
     * @var array
     */
    protected $PLENTY_name2ID = [];

    /**
     * @var array
     */
    protected $PLENTY_idAndValueName2ID = [];

    /**
     * Build an index of the exising attributes.
     */
    protected function index()
    {
        $Request_GetItemAttributes = new PlentySoapRequest_GetItemAttributes();
        $Request_GetItemAttributes->GetValues = true; // boolean
        $Request_GetItemAttributes->Lang = 'de';

        // Fetch the attributes form plentymarkets
        $Response_GetItemAttributes = PlentymarketsSoapClient::getInstance()->GetItemAttributes($Request_GetItemAttributes);

        if (!$Response_GetItemAttributes->Success) {
            throw new PlentymarketsExportException('The item attributes could not be retrieved', 2910);
        }

        foreach ($Response_GetItemAttributes->Attributes->item as $Attribute) {
            $this->PLENTY_name2ID[strtolower($Attribute->BackendName)] = $Attribute->Id;

            $this->PLENTY_idAndValueName2ID[$Attribute->Id] = [];
            foreach ($Attribute->Values->item as $AttributeValue) {
                $this->PLENTY_idAndValueName2ID[$Attribute->Id][strtolower($AttributeValue->BackendName)] = $AttributeValue->ValueId;
            }
        }
    }

    /**
     * Build the index and export the missing data to plentymarkets.
     */
    public function run()
    {
        $this->index();
        $this->doExport();
    }

    /**
     * @description Export the translation of the attributes values that are set for the language shops in shopware (TB: s_core_translation)
     *
     * @param int $plentyAttributeID
     * @param int $shopwareAttributeValueID
     * @param int $plentyAttributeValueID
     */
    private function exportAttributeValuesTranslations($plentyAttributeID, $shopwareAttributeValueID, $plentyAttributeValueID)
    {
        $mainShops = PlentymarketsUtils::getShopwareMainShops();

        $Request_SetItemAttributes = new PlentySoapRequest_SetItemAttributes();

        /** @var $mainShop Shopware\Models\Shop\Shop */
        foreach ($mainShops as $mainShop) {
            // get all active languages of the main shop
            $activeLanguages = PlentymarketsTranslation::getShopActiveLanguages($mainShop->getId());

            foreach ($activeLanguages as $key => $language) {
                // export the atrribute value translations of the language shops and main shops

                // try to get translation
                $attrValueTranslation = PlentymarketsTranslation::getShopwareTranslation($mainShop->getId(), 'configuratoroption', $shopwareAttributeValueID, $key);

                // if the translation was found, do export
                if (!is_null($attrValueTranslation) && isset($attrValueTranslation['name'])) {
                    $Object_SetItemAttribute = new PlentySoapObject_SetItemAttribute();
                    $Object_SetItemAttribute->Id = $plentyAttributeID;
                    $Object_SetItemAttribute->FrontendLang = PlentymarketsTranslation::getPlentyLocaleFormat($language['locale']);

                    $Object_SetItemAttributeValue = new PlentySoapObject_SetItemAttributeValue();
                    $Object_SetItemAttributeValue->ValueId = $plentyAttributeValueID;
                    $Object_SetItemAttributeValue->FrontendName = $attrValueTranslation['name'];

                    $Object_SetItemAttribute->Values[] = $Object_SetItemAttributeValue;
                    $Request_SetItemAttributes->Attributes[] = $Object_SetItemAttribute;
                }
            }
        }

        if (!empty($Request_SetItemAttributes->Attributes)) {
            $Response = PlentymarketsSoapClient::getInstance()->SetItemAttributes($Request_SetItemAttributes);

            if (!$Response->Success) {
                // throw exception
            }
        }
    }

    /**
     * @description Export the translation of the attributes and attributes values that are set for the language shops in shopware
     *
     * @param int $shopwareAttributeID
     * @param int $plentyAttributeID
     */
    private function exportAttributeTranslations($shopwareAttributeID, $plentyAttributeID)
    {
        $Request_SetItemAttributes = new PlentySoapRequest_SetItemAttributes();

        $mainShops = PlentymarketsUtils::getShopwareMainShops();

        /** @var $mainShop Shopware\Models\Shop\Shop */
        foreach ($mainShops as $mainShop) {
            $Request_SetItemAttributes = new PlentySoapRequest_SetItemAttributes();

            // get all active languages of the main shop
            $activeLanguages = PlentymarketsTranslation::getShopActiveLanguages($mainShop->getId());

            foreach ($activeLanguages as $key => $language) {
                // export the atrribute translations of the language shops and main shops

                // try to get translation
                $attrTranslation = PlentymarketsTranslation::getShopwareTranslation($mainShop->getId(), 'configuratorgroup', $shopwareAttributeID, $key);

                // if the translation was found, do export
                if (!is_null($attrTranslation) && isset($attrTranslation['name'])) {
                    $Object_SetItemAttribute = new PlentySoapObject_SetItemAttribute();
                    $Object_SetItemAttribute->Id = $plentyAttributeID;
                    $Object_SetItemAttribute->FrontendLang = PlentymarketsTranslation::getPlentyLocaleFormat($language['locale']);
                    $Object_SetItemAttribute->FrontendName = $attrTranslation['name'];

                    $Request_SetItemAttributes->Attributes[] = $Object_SetItemAttribute;
                }
            }
        }

        if (!empty($Request_SetItemAttributes->Attributes)) {
            $Response = PlentymarketsSoapClient::getInstance()->SetItemAttributes($Request_SetItemAttributes);

            if (!$Response->Success) {
                // throw exception
            }
        }
    }

    /**
     * Export the missing attribtues to plentymarkets and save the mapping.
     */
    protected function doExport()
    {
        // Repository
        $Repository = Shopware()->Models()->getRepository('Shopware\Models\Article\Configurator\Group');

        // Chunk configuration
        $chunk = 0;
        $size = PlentymarketsConfig::getInstance()->getInitialExportChunkSize(PlentymarketsExportController::DEFAULT_CHUNK_SIZE);

        do {
            PlentymarketsLogger::getInstance()->message('Export:Initial:Attribute', 'Chunk: '.($chunk + 1));
            $Groups = $Repository->findBy([], null, $size, $chunk * $size);

            /** @var Shopware\Models\Article\Configurator\Group $Attribute */
            foreach ($Groups as $Attribute) {
                $Request_SetItemAttributes = new PlentySoapRequest_SetItemAttributes();

                $Object_SetItemAttribute = new PlentySoapObject_SetItemAttribute();
                $Object_SetItemAttribute->BackendName = sprintf('%s (Sw %d)', $Attribute->getName(), $Attribute->getId());
                $Object_SetItemAttribute->FrontendLang = 'de';
                $Object_SetItemAttribute->FrontendName = $Attribute->getName();
                $Object_SetItemAttribute->Position = $Attribute->getPosition();

                try {
                    $attributeIdAdded = PlentymarketsMappingController::getAttributeGroupByShopwareID($Attribute->getId());
                } catch (PlentymarketsMappingExceptionNotExistant $E) {
                    if (isset($this->PLENTY_name2ID[strtolower($Object_SetItemAttribute->BackendName)])) {
                        $attributeIdAdded = $this->PLENTY_name2ID[strtolower($Object_SetItemAttribute->BackendName)];
                    } else {
                        $Request_SetItemAttributes->Attributes[] = $Object_SetItemAttribute;
                        $Response = PlentymarketsSoapClient::getInstance()->SetItemAttributes($Request_SetItemAttributes);

                        if (!$Response->Success) {
                            throw new PlentymarketsExportException('The item attribute »'.$Object_SetItemAttribute->BackendName.'« could not be created', 2911);
                        }

                        $attributeIdAdded = (int) $Response->ResponseMessages->item[0]->SuccessMessages->item[0]->Value;
                    }

                    // Add the mapping
                    PlentymarketsMappingController::addAttributeGroup($Attribute->getId(), $attributeIdAdded);

                    $this->exportAttributeTranslations($Attribute->getId(), $attributeIdAdded);
                }

                // Values
                /** @var Shopware\Models\Article\Configurator\Option $AttributeValue */
                foreach ($Attribute->getOptions() as $AttributeValue) {
                    $Request_SetItemAttributes = new PlentySoapRequest_SetItemAttributes();

                    $Object_SetItemAttribute = new PlentySoapObject_SetItemAttribute();
                    $Object_SetItemAttribute->Id = $attributeIdAdded;

                    $Object_SetItemAttributeValue = new PlentySoapObject_SetItemAttributeValue();
                    $Object_SetItemAttributeValue->BackendName = sprintf('%s (Sw %d)', $AttributeValue->getName(), $AttributeValue->getId());
                    $Object_SetItemAttributeValue->FrontendName = $AttributeValue->getName();
                    $Object_SetItemAttributeValue->Position = $AttributeValue->getPosition();

                    $Object_SetItemAttribute->Values[] = $Object_SetItemAttributeValue;
                    $Request_SetItemAttributes->Attributes[] = $Object_SetItemAttribute;

                    try {
                        PlentymarketsMappingController::getAttributeOptionByShopwareID($AttributeValue->getId());
                    } catch (PlentymarketsMappingExceptionNotExistant $E) {
                        // Workaround
                        $checknameValue = strtolower(str_replace(',', '.', $Object_SetItemAttributeValue->BackendName));

                        if (isset($this->PLENTY_idAndValueName2ID[$attributeIdAdded][$checknameValue])) {
                            PlentymarketsMappingController::addAttributeOption($AttributeValue->getId(), $this->PLENTY_idAndValueName2ID[$attributeIdAdded][$checknameValue]);
                        } else {
                            $Response = PlentymarketsSoapClient::getInstance()->SetItemAttributes($Request_SetItemAttributes);

                            if (!$Response->Success) {
                                throw new PlentymarketsExportException('The item attribute option »'.$Object_SetItemAttributeValue->BackendName.'« could not be created', 2912);
                            }

                            foreach ($Response->ResponseMessages->item[0]->SuccessMessages->item as $MessageItem) {
                                if ($MessageItem->Key == 'AttributeValueID') {
                                    PlentymarketsMappingController::addAttributeOption($AttributeValue->getId(), $MessageItem->Value);

                                    $this->exportAttributeValuesTranslations($attributeIdAdded, $AttributeValue->getId(), $MessageItem->Value);
                                }
                            }
                        }
                    }
                }
            }

            ++$chunk;
        } while (!empty($Groups) && count($Groups) == $size);
    }

    /**
     * Checks whether the export is finished.
     *
     * @return bool
     */
    public function isFinished()
    {
        return true;
    }
}
