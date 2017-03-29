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

/**
 * The class PlentymarketsImportController does the actual import for different cronjobs e.g. in the class PlentymarketsCronjobController.
 * It uses the different import entities in /Import/Entity respectively in /Import/Entity/Order, for example PlentymarketsImportEntityItem.
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsImportControllerItem
{
    /**
     * @var int
     */
    const DEFAULT_CHUNK_SIZE = 250;

    /**
     * @var array
     */
    protected $itemIdsDone = [];

    /**
     * imports the item for the given shop
     *
     * @param int $itemId
     * @param int $storeId
     *
     * @throws PlentymarketsImportException
     */
    public function importItem($itemId, $storeId)
    {
        // Check whether the item has already been imported
        $full = !isset($this->itemIdsDone[$itemId]);

        // Build the request
        $Request_GetItemsBase = new PlentySoapRequest_GetItemsBase();
        $Request_GetItemsBase->GetAttributeValueSets = $full;
        $Request_GetItemsBase->GetCategories = true;
        $Request_GetItemsBase->GetCategoryNames = true;
        $Request_GetItemsBase->GetItemAttributeMarkup = $full;
        $Request_GetItemsBase->GetItemOthers = $full;
        //$Request_GetItemsBase->GetItemProperties = $full;
        $Request_GetItemsBase->GetItemProperties = true;
        $Request_GetItemsBase->GetItemSuppliers = false;
        $Request_GetItemsBase->GetItemURL = 0;
        $Request_GetItemsBase->GetLongDescription = $full;
        $Request_GetItemsBase->GetMetaDescription = false;
        $Request_GetItemsBase->GetShortDescription = $full;
        $Request_GetItemsBase->GetTechnicalData = false;
        $Request_GetItemsBase->StoreID = $storeId;
        $Request_GetItemsBase->ItemID = $itemId;

        // get the main language of the shop
    //$mainLang = array_values(PlentymarketsTranslation::getInstance()->getShopMainLanguage(PlentymarketsMappingController::getShopByPlentyID($storeId)));
        // set the main language of the shop in soap request
    //$Request_GetItemsBase->Lang = PlentymarketsTranslation::getInstance()->getPlentyLocaleFormat($mainLang[0]['locale']);

        $Request_GetItemsBase->Lang = 'de';

        // Allow plugins to change the data
        $Request_GetItemsBase = Enlight()->Events()->filter(
            'PlentyConnector_ImportControllerItem_AfterCreateGetItemBaseRequest',
            $Request_GetItemsBase,
            [
                'subject' => $this,
                'itemid' => $itemId,
                'storeid' => $storeId,
            ]
        );

        // Do the request
        $Response_GetItemsBase = PlentymarketsSoapClient::getInstance()->GetItemsBase($Request_GetItemsBase);

        // On error
        if ($Response_GetItemsBase->Success == false) {
            // Re-add the item to the stack and quit
            PlentymarketsImportStackItem::getInstance()->addItem($itemId, $storeId);

            return;
        }

        // Item not found
        if (!isset($Response_GetItemsBase->ItemsBase->item[0])) {
            return;
        }

        $ItemBase = $Response_GetItemsBase->ItemsBase->item[0];

        // Skip bundles
        $skipBundles = PlentymarketsConfig::getInstance()->getItemBundleHeadActionID(IMPORT_ITEM_BUNDLE_HEAD_NO) == IMPORT_ITEM_BUNDLE_HEAD_NO;

        // Allow plugins to change the data
        $skipBundles = Shopware()->Events()->filter(
            'PlentyConnector_ImportControllerItem_FilterSkipBundles',
            $skipBundles,
            [
                'subject' => $this,
                'itemid' => $itemId,
                'storeid' => $storeId,
                'itembase' => $ItemBase,
            ]
        );

        if ($ItemBase->BundleType === 'bundle' && $skipBundles) {
            PlentymarketsLogger::getInstance()->message('Sync:Item', 'The item »' . $ItemBase->Texts->Name . '« will be skipped (bundle)');

            return;
        }

        // get the item texts in all active languages
        $itemTexts = [];
        $shopId = PlentymarketsMappingController::getShopByPlentyID($storeId);

        //if this is a main shop , get the item texts translation for its main language and its shop languages
        if (PlentymarketsTranslation::isMainShop($shopId)) {
            // get all active languages of the shop (from shopware)
            $activeLanguages = PlentymarketsTranslation::getShopActiveLanguages($shopId);

            foreach ($activeLanguages as $localeId => $language) {
                $Request_GetItemsTexts = new PlentySoapRequest_GetItemsTexts();
                $Request_GetItemsTexts->ItemsList = [];

                $Object_RequestItems = new PlentySoapObject_RequestItems();
                $Object_RequestItems->ExternalItemNumer = null; // string
                $Object_RequestItems->ItemId = $itemId; // string
                $Object_RequestItems->ItemNumber = null; // string
                $Object_RequestItems->Lang = PlentymarketsTranslation::getPlentyLocaleFormat($language['locale']); // string
                $Request_GetItemsTexts->ItemsList[] = $Object_RequestItems;

                $Response_GetItemsTexts = PlentymarketsSoapClient::getInstance()->GetItemsTexts($Request_GetItemsTexts);

                if (isset($Response_GetItemsTexts->ItemTexts->item[0])) {
                    $itemText = [];
                    // save the language infos for the item texts
                    $itemText['locale'] = $language['locale'];

                    // if mainShopId == null, then it is the main shop and no language shop
                    // each language shop has a mainShopId
                    if (!is_null($language['mainShopId'])) {
                        $itemText['languageShopId'] = PlentymarketsTranslation::getLanguageShopID($localeId, $language['mainShopId']);
                    } elseif (PlentymarketsTranslation::getPlentyLocaleFormat($language['locale']) != 'de') {
                        // set the language for the main shop if the main language is not German
                        $itemText['languageShopId'] = $shopId;
                    }

                    $itemText['texts'] = $Response_GetItemsTexts->ItemTexts->item[0];

                    $itemTexts[] = $itemText;
                }
            }
        }

        try {
            $shopId = PlentymarketsMappingController::getShopByPlentyID($storeId);
            $Shop = Shopware()->Models()->find('Shopware\Models\Shop\Shop', $shopId);

            $Importuer = new PlentymarketsImportEntityItem($ItemBase, $Shop);

            // The item has already been updated
            if (!$full) {
                // so we just need to do the categories
                $Importuer->importCategories();

                //if this is a main shop , import the translation for its main language and its shop languages
                if (PlentymarketsTranslation::isMainShop($shopId)) {
                    if (!empty($itemTexts)) {
                        // Do the import for item texts translation
                        $Importuer->saveItemTextsTranslation($itemTexts);
                    }

                    // Do the import for the property value translations
                    $Importuer->importItemPropertyValueTranslations();
                }
            } else {
                // Do a full import
                $Importuer->import();

                //if this is a main shop , import the translation for its main language and its shop languages
                if (PlentymarketsTranslation::isMainShop($shopId)) {
                    if (!empty($itemTexts)) {
                        // Do the import for item texts translation
                        $Importuer->saveItemTextsTranslation($itemTexts);
                    }

                    // Do the import for the property value translations
                    $Importuer->importItemPropertyValueTranslations();
                }

                // Add it to the link controller
                PlentymarketsImportControllerItemLinked::getInstance()->addItem($ItemBase->ItemID);

                // Mark this item as done
                $this->itemIdsDone[$ItemBase->ItemID] = true;
            }

            // Log the usage data
            PyLog()->usage();
        } catch (Shopware\Components\Api\Exception\ValidationException $E) {
            PlentymarketsLogger::getInstance()->error('Sync:Item:Validation', 'The item »' . $ItemBase->Texts->Name . '« with the id »' . $ItemBase->ItemID . '« could not be imported', 3010);
            foreach ($E->getViolations() as $ConstraintViolation) {
                PlentymarketsLogger::getInstance()->error('Sync:Item:Validation', $ConstraintViolation->getMessage());
                PlentymarketsLogger::getInstance()->error('Sync:Item:Validation', $ConstraintViolation->getPropertyPath() . ': ' . $ConstraintViolation->getInvalidValue());
            }
        } catch (Shopware\Components\Api\Exception\OrmException $E) {
            PlentymarketsLogger::getInstance()->error('Sync:Item:Orm', 'The item »' . $ItemBase->Texts->Name . '« with the id »' . $ItemBase->ItemID . '« could not be imported (' . $E->getMessage() . ')', 3020);
            PlentymarketsLogger::getInstance()->error('Sync:Item:Orm', $E->getTraceAsString(), 1000);
            throw new PlentymarketsImportException('The item import will be stopped (internal database error)', 3021);
        } catch (PlentymarketsImportItemNumberException $E) {
            PlentymarketsLogger::getInstance()->error('Sync:Item:Number', $E->getMessage(), $E->getCode());
        } catch (PlentymarketsImportItemException $E) {
            PlentymarketsLogger::getInstance()->error('Sync:Item:Number', $E->getMessage(), $E->getCode());
        } catch (Exception $E) {
            PlentymarketsLogger::getInstance()->error('Sync:Item', 'The item »' . $ItemBase->Texts->Name . '« with the id »' . $ItemBase->ItemID . '« could not be imported', 3000);
            PlentymarketsLogger::getInstance()->error('Sync:Item', $E->getTraceAsString(), 1000);
            PlentymarketsLogger::getInstance()->error('Sync:Item', get_class($E));
            PlentymarketsLogger::getInstance()->error('Sync:Item', $E->getMessage());
        }
    }

    /**
     * Finalizes the import
     */
    public function finish()
    {
        try {
            // Stock stack
            PlentymarketsImportItemStockStack::getInstance()->import();
        } catch (Exception $E) {
            PlentymarketsLogger::getInstance()->error('Sync:Item:Stock', 'PlentymarketsImportItemStockStack failed');
            PlentymarketsLogger::getInstance()->error('Sync:Item:Stock', $E->getMessage());
        }

        try {
            // Stock stack
            PlentymarketsImportControllerItemLinked::getInstance()->run();
        } catch (Exception $E) {
            PlentymarketsLogger::getInstance()->error('Sync:Item:Linked', 'PlentymarketsImportControllerItemLinked failed');
            PlentymarketsLogger::getInstance()->error('Sync:Item:Linked', $E->getMessage());
        }

        try {
            // Stock stack
            PlentymarketsImportItemImageThumbnailController::getInstance()->generate();
        } catch (Exception $E) {
            PlentymarketsLogger::getInstance()->error('Sync:Item:Image', 'PlentymarketsImportItemImageThumbnailController failed');
            PlentymarketsLogger::getInstance()->error('Sync:Item:Image', $E->getMessage());
        }
    }

    /**
     * Reads the items of plentymarkets that have changed
     */
    public function run()
    {
        // Number of items
        $chunkSize = PlentymarketsConfig::getInstance()->getImportItemChunkSize(self::DEFAULT_CHUNK_SIZE);

        // get the chunk out of the stack
        $chunk = PlentymarketsImportStackItem::getInstance()->getChunk($chunkSize);

        // Import each item
        try {
            while (($item = array_shift($chunk)) && is_array($item)) {
                // for each assigned store
                $storeIds = explode('|', $item['storeIds']);
                foreach ($storeIds as $storeId) {
                    // Import the item
                    $this->importItem($item['itemId'], $storeId);
                }
            }
        } catch (PlentymarketsImportException $E) {
            PlentymarketsLogger::getInstance()->error('Sync:Item', $E->getMessage(), $E->getCode());

            // return to the stack
            foreach ($chunk as $item) {
                // for each assigned store
                $storeIds = explode('|', $item['storeIds']);
                foreach ($storeIds as $storeId) {
                    // Import the item
                    PlentymarketsImportStackItem::getInstance()->addItem($item['itemId'], $storeId);
                }
            }

            PlentymarketsLogger::getInstance()->message('Sync:Stack:Item', 'Returned ' . count($chunk) . ' items to the stack');
        }

        $numberOfItems = count($this->itemIdsDone);

        // Log
        if ($numberOfItems == 0) {
            PlentymarketsLogger::getInstance()->message('Sync:Item', 'No item has been updated or created.');
        } elseif ($numberOfItems == 1) {
            PlentymarketsLogger::getInstance()->message('Sync:Item', '1 item has been updated or created.');
        } else {
            PlentymarketsLogger::getInstance()->message('Sync:Item', $numberOfItems . ' items have been updated or created.');
        }

        // Log stack information
        $stackSize = count(PlentymarketsImportStackItem::getInstance());
        if ($stackSize == 1) {
            PlentymarketsLogger::getInstance()->message('Sync:Stack:Item', '1 item left in the stack');
        } elseif ($stackSize > 1) {
            PlentymarketsLogger::getInstance()->message('Sync:Stack:Item', $stackSize . ' items left in the stack');
        } else {
            PlentymarketsLogger::getInstance()->message('Sync:Stack:Item', 'The stack is empty');
        }

        // Post processed
        $this->finish();
    }
}
