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
 * PlentymarketsExportControllerItemCategory provides the actual items export functionality. Like the other export
 * entities this class is called in PlentymarketsExportController.
 * The data export takes place based on plentymarkets SOAP-calls.
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsExportControllerItemCategory
{
    /**
     * @var array
     */
    protected $mappingShopwareID2PlentyID = [];

    /**
     * @var array
     */
    protected $PLENTY_CategoryTree2ShopID = [];

    /**
     * Build the index and export the missing data to plentymarkets
     */
    public function run()
    {
        $this->index();
        $this->main();
    }

    /**
     * Checks whether the export is finished
     *
     * @return bool
     */
    public function isFinished()
    {
        return true;
    }

    /**
     * Build an index of the existing data
     *
     * @todo language
     */
    protected function index()
    {
        $Request_GetItemCategoryTree = new PlentySoapRequest_GetItemCategoryTree();
        $Request_GetItemCategoryTree->Lang = 'de';
        $Request_GetItemCategoryTree->GetCategoryNames = true;

        /** @var PlentySoapResponse_GetItemCategoryTree $Response_GetItemCategoryTree */
        $Response_GetItemCategoryTree = PlentymarketsSoapClient::getInstance()->GetItemCategoryTree($Request_GetItemCategoryTree);

        if (!$Response_GetItemCategoryTree->Success) {
            throw new PlentymarketsExportException('The item category tree could not be retrieved', 2920);
        }

        $plenty_nameAndLevel2ID = ['children' => []];

        /** @var PlentySoapObject_ItemCategoryTreeNode $Category */
        foreach ($Response_GetItemCategoryTree->MultishopTree->item[0]->CategoryTree->item as $Category) {
            $index = &$plenty_nameAndLevel2ID;
            $categoryPath = explode(';', $Category->CategoryPath);
            $categoryPathNames = explode(';', $Category->CategoryPathNames);
            $branchId = 0;

            foreach ($categoryPath as $n => $categoryId) {
                if ($categoryId == 0) {
                    break;
                }

                $branchId = $categoryId;
                $categoryName = $categoryPathNames[$n];
                if (!isset($index['children'][$categoryName])) {
                    $index['children'][$categoryName] = [
                        'id' => $categoryId,
                        'children' => [],
                    ];
                }
                $index = &$index['children'][$categoryName];
            }

            $index = [
                'id' => $branchId,
            ];
        }

        $this->PLENTY_CategoryTree2ShopID = $plenty_nameAndLevel2ID;
    }

    /**
     * Export the missing categories from shopware to plenty
     */
    protected function main()
    {
        $shopwareCategories = Shopware()->Models()
            ->getRepository('Shopware\Models\Category\Category')
            ->findBy(['path' => null]);

        /** @var Shopware\Models\Category\Category $shopwareCategory */
        foreach ($shopwareCategories as $shopwareCategory) {
            // Skip "Root"
            if ((int) $shopwareCategory->getParentId() == 0) {
                continue;
            }

            if ($shopwareCategory->getBlog()) {
                continue;
            }

            // Get the store for this category
            $rootId = PlentymarketsUtils::getRootIdByCategory($shopwareCategory);
            $shops = PlentymarketsUtils::getShopIdByCategoryRootId($rootId);

            if (!$shops) {
                $shops = [0];
            }

            foreach ($shops as $shopId) {
                try {
                    $storeId = PlentymarketsMappingController::getShopByShopwareID($shopId);
                } catch (PlentymarketsMappingExceptionNotExistant $E) {
                    $storeId = 0;
                }

                $this->export($shopwareCategory->getChildren(), $this->PLENTY_CategoryTree2ShopID, $storeId);
            }
        }
    }

    /**
     * Creates the category in plentymarkets
     *
     * @param \Shopware\Models\Category\Category $category
     * @param $storeId
     * @param $shopId
     * @param null $categoryId
     *
     * @throws PlentymarketsExportException
     *
     * @return int|null
     */
    private function exportCategory(Shopware\Models\Category\Category $category, $storeId, $shopId, $categoryId = null)
    {
        $level = $category->getLevel() - 1;

        if ($level == 1) {
            $parentId = null;
        } else {
            $parentId = PlentymarketsMappingEntityCategory::getCategoryByShopwareID($category->getParentId(), $shopId);
        }

        $Request_SetCategories = new PlentySoapRequest_SetCategories();

        $Request_SetCategories->SetCategories = [];

        $RequestObject_SetCategories = new PlentySoapRequestObject_SetCategories();
        $RequestObject_SetCategories->CategoryID = $categoryId;

        $RequestObject_CreateCategory = new PlentySoapRequestObject_CreateCategory();
        $RequestObject_CreateCategory->Description = null; // string
        $RequestObject_CreateCategory->Description2 = null; // string
        $RequestObject_CreateCategory->FulltextActive = null; // string
        $RequestObject_CreateCategory->Image = null; // string
        $RequestObject_CreateCategory->Image1Path = null; // string
        $RequestObject_CreateCategory->Image2 = null; // string
        $RequestObject_CreateCategory->Image2Path = null; // string
        $RequestObject_CreateCategory->ItemListView = null; // string
        $RequestObject_CreateCategory->Lang = 'de'; // string
        $RequestObject_CreateCategory->Level = $level; // int
        $RequestObject_CreateCategory->MetaDescription = $category->getMetaDescription(); // string
        $RequestObject_CreateCategory->MetaKeywords = $category->getMetaKeywords(); // string
        $RequestObject_CreateCategory->MetaTitle = $category->getName(); // string
        $RequestObject_CreateCategory->Name = $category->getName() ?: 'Category ' . $category->getId(); // string
        $RequestObject_CreateCategory->NameURL = null; // string
        $RequestObject_CreateCategory->PageView = null; // string
        $RequestObject_CreateCategory->PlaceholderTranslation = null; // string
        $RequestObject_CreateCategory->Position = $category->getPosition(); // int
        $RequestObject_CreateCategory->PreviewPath = null; // string
        $RequestObject_CreateCategory->RootPath = null; // string
        $RequestObject_CreateCategory->ShortDescription = null; // string
        $RequestObject_CreateCategory->SingleItemView = null; // string
        $RequestObject_CreateCategory->WebTemplateExist = null; // string
        $RequestObject_CreateCategory->WebstoreID = $storeId; // int
        $RequestObject_CreateCategory->ParentCategoryID = $parentId; //int

        $RequestObject_SetCategories->CreateCategory = $RequestObject_CreateCategory;

        $Request_SetCategories->SetCategories[] = $RequestObject_SetCategories;

        $Response_SetCategories = PlentymarketsSoapClient::getInstance()->SetCategories($Request_SetCategories);

        if (!$Response_SetCategories->Success) {
            throw new PlentymarketsExportException('The category could not be saved! ', 2920);
        }

        $categoryId = (int) $Response_SetCategories->Categories->item[0]->CategoryID;
        $shopId = PlentymarketsMappingController::getShopByPlentyID($storeId);
        PlentymarketsMappingEntityCategory::addCategory(
                $category->getId(), $shopId, $categoryId, $storeId
            );

        return $categoryId;
    }

    /**
     * Exports the categories
     *
     * @param $shopwareCategories
     * @param $plentyTree
     * @param $storeId
     */
    private function export($shopwareCategories, $plentyTree, $storeId)
    {
        try {
            $shopId = PlentymarketsMappingController::getShopByPlentyID($storeId);
        } catch (PlentymarketsMappingExceptionNotExistant $e) {
            return;
        }

        /** @var Shopware\Models\Category\Category $shopwareCategory */
        foreach ($shopwareCategories as $shopwareCategory) {
            if ($shopwareCategory->getBlog()) {
                continue;
            }

            try {
                PlentymarketsMappingEntityCategory::getCategoryByShopwareID($shopwareCategory->getId(), $shopId);
                continue;
            } catch (PlentymarketsMappingExceptionNotExistant $e) {
            }

            $branchId = null;

            if (!isset($plentyTree['children'])) {
                $plentyTree['children'] = [];
            }

            $shopwareName = trim($shopwareCategory->getName());

            /** @var array $plentyChild1 */
            foreach ($plentyTree['children'] as $name => $plentyChild1) {
                if ($name == $shopwareName) {
                    $branchId = $plentyChild1['id'];
                    PlentymarketsMappingEntityCategory::addCategory(
                        $shopwareCategory->getId(), $shopId, $branchId, $storeId
                    );
                    break;
                }
            }

            $branchId = $this->exportCategory($shopwareCategory, $storeId, $shopId, $branchId);

            // Active the category
            if ($storeId > 0 && $shopwareCategory->getActive()) {
                $Request_SetStoreCategories = new PlentySoapRequest_SetStoreCategories();
                $Request_SetStoreCategories->StoreCategories = [];

                $Object_SetStoreCategory = new PlentySoapObject_SetStoreCategory();
                $Object_SetStoreCategory->BranchID = $branchId;
                $Object_SetStoreCategory->StoreID = $storeId;

                $Request_SetStoreCategories->StoreCategories[] = $Object_SetStoreCategory;
                PlentymarketsSoapClient::getInstance()->SetStoreCategories($Request_SetStoreCategories);
            }

            $shopwareChildren1 = $shopwareCategory->getChildren();

            // search for the next categories of shopware in plentymarkets
            $this->export($shopwareChildren1, $plentyChild1, $storeId);
        }
    }
}
