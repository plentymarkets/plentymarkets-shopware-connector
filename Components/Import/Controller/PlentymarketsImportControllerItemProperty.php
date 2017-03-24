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
 * @copyright Copyright (c) 2013, plentymarkets GmbH (http://www.plentymarkets.com)
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */

/**
 * Imports the item properties.
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsImportControllerItemProperty
{
    /**
     * Performs the actual import.
     *
     * @param int $lastUpdateTimestamp
     */
    public function run($lastUpdateTimestamp)
    {
        $Request_GetPropertyGroups = new PlentySoapRequest_GetPropertyGroups();
        $Request_GetPropertyGroups->Lang = 'de';
        $Request_GetPropertyGroups->LastUpdateFrom = $lastUpdateTimestamp;
        $Request_GetPropertyGroups->Page = 0;

        do {
            /** @var PlentySoapResponse_GetPropertyGroups $Response_GetPropertyGroups */
            $Response_GetPropertyGroups = PlentymarketsSoapClient::getInstance()->GetPropertyGroups($Request_GetPropertyGroups);

            foreach ($Response_GetPropertyGroups->PropertyGroups->item as $Option) {
                $PlentymarketsImportEntityItemPropertyGroup = new PlentymarketsImportEntityItemPropertyGroup($Option);
                $PlentymarketsImportEntityItemPropertyGroup->import();
            }
        } while (++$Request_GetPropertyGroups->Page < $Response_GetPropertyGroups->Pages);

        $Request_GetProperties = new PlentySoapRequest_GetProperties();
        $Request_GetProperties->Lang = 'de';
        $Request_GetProperties->LastUpdateFrom = $lastUpdateTimestamp;
        $Request_GetProperties->Page = 0;

        do {
            /** @var PlentySoapResponse_GetProperties $Response_GetProperties */
            $Response_GetProperties = PlentymarketsSoapClient::getInstance()->GetProperties($Request_GetProperties);

            foreach ($Response_GetProperties->Properties->item as $Option) {
                $PlentymarketsImportEntityItemPropertyOption = new PlentymarketsImportEntityItemPropertyOption($Option);
                $PlentymarketsImportEntityItemPropertyOption->import();
            }
        } while (++$Request_GetProperties->Page < $Response_GetProperties->Pages);

        $this->runImportTranslations($lastUpdateTimestamp);
    }

    /**
     * Run import of property groups and property translations.
     *
     * @param int $lastUpdateTimestamp
     */
    private function runImportTranslations($lastUpdateTimestamp)
    {
        $mainShops = PlentymarketsUtils::getShopwareMainShops();

        /** @var $mainShop Shopware\Models\Shop\Shop */
        foreach ($mainShops as $mainShop) {
            // get all active languages of the main shop
            $activeLanguages = PlentymarketsTranslation::getShopActiveLanguages($mainShop->getId());

            foreach ($activeLanguages as $key => $language) {
                // import tanslation for property group

                $Request_GetPropertyGroups = new PlentySoapRequest_GetPropertyGroups();
                $Request_GetPropertyGroups->Lang = PlentymarketsTranslation::getPlentyLocaleFormat($language['locale']);
                $Request_GetPropertyGroups->LastUpdateFrom = $lastUpdateTimestamp;
                $Request_GetPropertyGroups->Page = 0;

                do {
                    /** @var PlentySoapResponse_GetPropertyGroups $Response_GetPropertyGroups */
                    $Response_GetPropertyGroups = PlentymarketsSoapClient::getInstance()->GetPropertyGroups($Request_GetPropertyGroups);

                    foreach ($Response_GetPropertyGroups->PropertyGroups->item as $group) {
                        $PlentymarketsImportEntityItemPropertyGroup = new PlentymarketsImportEntityItemPropertyGroup($group);

                        // set the property group translations from plenty for the language shops
                        if (!is_null($language['mainShopId'])) {
                            $languageShopID = PlentymarketsTranslation::getLanguageShopID($key, $language['mainShopId']);
                            $PlentymarketsImportEntityItemPropertyGroup->importPropertyGroupTranslation($languageShopID);
                        } else {
                            $PlentymarketsImportEntityItemPropertyGroup->importPropertyGroupTranslation($mainShop->getId());
                        }
                    }
                } while (++$Request_GetPropertyGroups->Page < $Response_GetPropertyGroups->Pages);

                // import translation for properties

                $Request_GetProperties = new PlentySoapRequest_GetProperties();
                $Request_GetProperties->Lang = PlentymarketsTranslation::getPlentyLocaleFormat($language['locale']);
                $Request_GetProperties->LastUpdateFrom = $lastUpdateTimestamp;
                $Request_GetProperties->Page = 0;

                do {
                    /** @var PlentySoapResponse_GetProperties $Response_GetProperties */
                    $Response_GetProperties = PlentymarketsSoapClient::getInstance()->GetProperties($Request_GetProperties);

                    foreach ($Response_GetProperties->Properties->item as $Option) {
                        $PlentymarketsImportEntityItemPropertyOption = new PlentymarketsImportEntityItemPropertyOption($Option);

                        // set the property translations from plenty for the language shops
                        if (!is_null($language['mainShopId'])) {
                            $languageShopID = PlentymarketsTranslation::getLanguageShopID($key, $language['mainShopId']);
                            $PlentymarketsImportEntityItemPropertyOption->importPropertyTranslation($languageShopID);
                        } else {
                            // set the property translation for the main shop
                            $PlentymarketsImportEntityItemPropertyOption->importPropertyTranslation($mainShop->getId());
                        }
                    }
                } while (++$Request_GetProperties->Page < $Response_GetProperties->Pages);
            }
        }
    }
}
