<?php


/**
 * Imports a item bundle.
 *
 * Class PlentymarketsImportEntityItemBundle
 */
class PlentymarketsImportEntityItemBundle
{
    /**
     * @var array
     */
    protected $SHOPWARE_bundleItemDetailList = [];

    /**
     * @var PlentySoapObject_Bundle
     */
    protected $PLENTY_bundle;

    /**
     * @var int
     */
    protected $PLENTY_bundleHeadId;

    /**
     * I am the constructor.
     *
     * @param PlentySoapObject_Bundle $PlentySoapObject_Bundle
     */
    public function __construct($PlentySoapObject_Bundle)
    {
        $this->PLENTY_bundle = $PlentySoapObject_Bundle;
    }

    /**
     * Public method to start the actual import.
     */
    public function import()
    {
        $this->index();
        $this->importBundle();
    }

    /**
     * Builds an index and checks if all items are present.
     *
     * @throws Exception
     */
    protected function index()
    {
        $bundleHeadSku = explode('-', $this->PLENTY_bundle->SKU);
        $this->PLENTY_bundleHeadId = (int) $bundleHeadSku[0];

        // Check whether all bundle items are present in shopware
        foreach ($this->PLENTY_bundle->Items->item as $PlentySoapObject_BundleItem) {
            /** @var PlentySoapObject_BundleItem $PlentySoapObject_BundleItem */
            $bundleItemSku = explode('-', $PlentySoapObject_BundleItem->SKU);
            $plentyBundleItemId = $bundleItemSku[0];

            try {
                // Variant
                if (isset($bundleItemSku[2]) && $bundleItemSku[2] > 0) {
                    $shopwareBundleItemDetailId = PlentymarketsMappingController::getItemVariantByPlentyID($PlentySoapObject_BundleItem->SKU);

                    // The detail is needed
                    $detail = Shopware()->Models()->find('Shopware\Models\Article\Detail', $shopwareBundleItemDetailId);
                    $isVariant = true;
                }

                // Base item
                else {
                    $shopwareBundleItemId = PlentymarketsMappingController::getItemByPlentyID($plentyBundleItemId);

                    /** @var Shopware\Models\Article\Article $shopwareItem */
                    $shopwareItem = Shopware()->Models()->find('Shopware\Models\Article\Article', $shopwareBundleItemId);

                    // The detail is needed
                    $detail = $shopwareItem->getMainDetail();
                    $isVariant = false;
                }
            } catch (PlentymarketsMappingExceptionNotExistant $E) {
                throw new PlentymarketsImportException('The item bundle with SKU »'.$this->PLENTY_bundle->SKU.'« can not be imported. Not all of the items included in the bundle are available in shopware.', 3710);
            }

            $this->SHOPWARE_bundleItemDetailList[$detail->getId()] = [
                'detail'    => $detail,
                'quantity'  => (int) $PlentySoapObject_BundleItem->Quantity,
                'isVariant' => $isVariant,
            ];
        }
    }

    /**
     * Imports the item bundle.
     *
     * @throws Exception
     */
    protected function importBundle()
    {
        // Get the bundle head
        $Request_GetItemsBase = new PlentySoapRequest_GetItemsBase();
        $Request_GetItemsBase->GetAttributeValueSets = false;
        $Request_GetItemsBase->GetCategories = false;
        $Request_GetItemsBase->GetCategoryNames = false;
        $Request_GetItemsBase->GetItemAttributeMarkup = false;
        $Request_GetItemsBase->GetItemOthers = false;
        $Request_GetItemsBase->GetItemProperties = false;
        $Request_GetItemsBase->GetItemSuppliers = false;
        $Request_GetItemsBase->GetItemURL = 0;
        $Request_GetItemsBase->GetLongDescription = false;
        $Request_GetItemsBase->GetMetaDescription = false;
        $Request_GetItemsBase->GetShortDescription = false;
        $Request_GetItemsBase->GetTechnicalData = false;
        $Request_GetItemsBase->ItemID = $this->PLENTY_bundleHeadId;
        $Request_GetItemsBase->Page = 0;

        /** @var PlentySoapResponse_GetItemsBase $Response_GetItemsBase */
        $Response_GetItemsBase = PlentymarketsSoapClient::getInstance()->GetItemsBase($Request_GetItemsBase);

        if ($Response_GetItemsBase->Success == false || !isset($Response_GetItemsBase->ItemsBase->item[0])) {
            throw new PlentymarketsImportException('The item bundle with SKU »'.$this->PLENTY_bundle->SKU.'« can not be imported (SOAP call failed).', 3701);
        }

        /** @var PlentySoapObject_ItemBase $ItemBase */
        $ItemBase = $Response_GetItemsBase->ItemsBase->item[0];

        try {
            // Get the existing bundle
            $shopwareBundleId = PlentymarketsMappingController::getItemBundleByPlentyID($this->PLENTY_bundleHeadId);

            /** @var Shopware\CustomModels\Bundle\Bundle $Bundle */
            $Bundle = Shopware()->Models()->find('Shopware\CustomModels\Bundle\Bundle', $shopwareBundleId);

            $currentShopwareBundleHeadItemDetailId = $Bundle->getArticle()->getMainDetail()->getId();
            if (!isset($this->SHOPWARE_bundleItemDetailList[$currentShopwareBundleHeadItemDetailId])) {
                // If the item which is the bundle head in shopware
                // has been removed in plentymarkets, the bundle has to get a new
                // head item. If this is not possible, the bundle will be delete.
                try {
                    $mainDetail = $this->getShopwareBundleItemDetail();

                    /** @var Shopware\Models\Article\Article $Article */
                    $Article = $mainDetail->getArticle();

                    PyLog()->message('Sync:Item:Bundle', 'The item »'.$Article->getName().'« with the number »'.$mainDetail->getNumber().'« is now the master item of the item bundle with the number »'.$Bundle->getNumber().'«.');

                    $Bundle->setArticle($Article);
                } catch (PlentymarketsImportException $e) {
                    PlentymarketsMappingController::deleteItemBundleByShopwareID($Bundle->getId());

                    PyLog()->message('Sync:Item:Bundle', 'The item bundle with the number »'.$Bundle->getNumber().'« will be deleted because no item can be identified as the master item. The previous master item with the number »'.$Bundle->getArticle()->getMainDetail()->getNumber().'« is no longer part of the item bundle.');

                    // Delete the bundle
                    Shopware()->Models()->remove($Bundle);
                    Shopware()->Models()->flush();

                    throw $e;
                }
            }

            $action = 'update';
        } catch (PlentymarketsMappingExceptionNotExistant $E) {
            $action = 'create';

            // Create a new one
            $Bundle = new Shopware\CustomModels\Bundle\Bundle();
            $mainDetail = $this->getShopwareBundleItemDetail();

            /** @var Shopware\Models\Article\Article $Article */
            $Article = $mainDetail->getArticle();

            PyLog()->message('Sync:Item:Bundle', 'The item »'.$Article->getName().'« with the number »'.$mainDetail->getNumber().'« will be the master item of the item bundle with the number »'.$ItemBase->ItemNo.'«.');

            // Set the stuff which needs to be set just one
            $Bundle->setArticle($Article);
            $Bundle->setType(1);
            $Bundle->setDiscountType('abs');
            $Bundle->setQuantity(0);
            $Bundle->setCreated();
            $Bundle->setSells(0);

            if (method_exists($Bundle, 'setDisplayGlobal')) {
                $Bundle->setDisplayGlobal(true);
            }
        }

        //
        $Bundle->setName($ItemBase->Texts->Name);
        $Bundle->setShowName(0);
        $Bundle->setNumber($ItemBase->ItemNo);

        $isLimited = $ItemBase->Stock->Limitation == 1;
        $Bundle->setLimited($isLimited);

        $isActive = $ItemBase->Availability->Inactive == 0 && $ItemBase->Availability->Webshop == 1;
        $Bundle->setActive($isActive);

        /** @var Shopware\Models\Customer\Group $CG */
        $CG = $this->getCustomerGroup();

        $shopwareBundleHeadItemId = $Bundle->getArticle()->getId();
        $items = [];
        foreach ($Bundle->getArticles() as $item) {
            /** @var Shopware\CustomModels\Bundle\Article $item */
            $itemDetailId = $item->getArticleDetail()->getId();

            // Not in the bundle or already done
            if (!isset($this->SHOPWARE_bundleItemDetailList[$itemDetailId])) {
                continue;
            }

            $quantity = $this->SHOPWARE_bundleItemDetailList[$itemDetailId]['quantity'];

            // If it is also the main item, the quantity needs to be reduced by one
            if ($item->getArticleDetail()->getArticle()->getId() == $shopwareBundleHeadItemId) {
                // If there is just one, the item is skipped since it is the HEAD - it will not be added as an item
                if ($quantity == 1) {
                    unset($this->SHOPWARE_bundleItemDetailList[$itemDetailId]);
                    continue;
                }

                // If the amount is higher - reduce item - the item is the HEAD and inside the bundle
                // in plenty it is just inside the bundle
                $quantity -= 1;
            }

            // Unset the detail - the rest of this array will be added as new items to the bundle
            unset($this->SHOPWARE_bundleItemDetailList[$itemDetailId]);

            // Update the quantity if changed
            if ($item->getQuantity() != $quantity) {
                $item->setQuantity($quantity);
            }

            $items[] = $item;
        }

        // Add all items, which aren't yet in the bundle
        foreach ($this->SHOPWARE_bundleItemDetailList as $config) {
            /** @var Shopware\Models\Article\Detail $detail */
            $detail = $config['detail'];

            // If the head is inside the bundle too, the amount needs to be reduced
            if ($detail->getArticle()->getId() == $shopwareBundleHeadItemId) {
                if ($config['quantity'] > 1) {
                    $config['quantity'] -= 1;
                }

                // or skipped if it is just the one (only happens with new or reset bundles)
                elseif ($config['quantity'] == 1) {
                    continue;
                }
            }
            $ArticleNew = new Shopware\CustomModels\Bundle\Article();

            $ItemDetail = $detail;
            $quantity = $config['quantity'];

            $ArticleNew->setArticleDetail($ItemDetail);
            $ArticleNew->setQuantity($quantity);

            $items[] = $ArticleNew;
        }

        // Set the bundle items
        $Bundle->setArticles($items);

        $newPrice = $ItemBase->PriceSet->Price;
        $newPrice /= (100 + $ItemBase->PriceSet->VAT) / 100;

        $isPriceFound = false;
        $prices = [];
        foreach ($Bundle->getPrices() as $price) {
            /** @var Shopware\CustomModels\Bundle\Price $price */
            if ($price->getCustomerGroup()->getKey() == $CG->getKey()) {
                $price->setPrice($newPrice);
                $isPriceFound = true;
            }
            $prices[] = $price;
        }

        if (!$isPriceFound) {
            $Price = new Shopware\CustomModels\Bundle\Price();
            $Price->setBundle($Bundle);
            $Price->setCustomerGroup($CG);
            $Price->setPrice($newPrice);
            $prices[] = $Price;
            $Bundle->setPrices($prices);
        }

        $Bundle->setCustomerGroups([$CG]);

        Shopware()->Models()->persist($Bundle);
        Shopware()->Models()->flush();

        if ($action == 'create') {
            PlentymarketsMappingController::addItemBundle($Bundle->getId(), $this->PLENTY_bundleHeadId);
            PyLog()->message('Sync:Item:Bundle', 'The item bundle »'.$ItemBase->Texts->Name.'« with the number »'.$ItemBase->ItemNo.'« has been created');
        } else {
            PyLog()->message('Sync:Item:Bundle', 'The item bundle »'.$ItemBase->Texts->Name.'« with the number »'.$ItemBase->ItemNo.'« has been updated');
        }
    }

    /**
     * Returns a shopware item detail id to use as bundle head.
     *
     * @throws Exception
     *
     * @return int
     */
    private function getShopwareBundleItemDetail()
    {
        $shopwareBundleItemId = null;

        // 1. attempt - a base item with the quantity of 1
        foreach ($this->SHOPWARE_bundleItemDetailList as $itemDetailId => $itemDetail) {
            if ($itemDetail['quantity'] == 1 && !$itemDetail['isVariant']) {
                $shopwareBundleItemId = $itemDetailId;
                break;
            }
        }

        // 2. attempt - a base item with any quantity
        if (!$shopwareBundleItemId) {
            foreach ($this->SHOPWARE_bundleItemDetailList as $itemDetailId => $itemDetail) {
                if (!$itemDetail['isVariant']) {
                    $shopwareBundleItemId = $itemDetailId;
                    break;
                }
            }
        }

        // nothing was found - the bundle cannot be (re-)created
        if (!$shopwareBundleItemId) {
            throw new PlentymarketsImportException('The item bundle with the SKU »'.$this->PLENTY_bundle->SKU.'« could not be imported.', 3720);
        }

        return $this->SHOPWARE_bundleItemDetailList[$shopwareBundleItemId]['detail'];
    }

    /**
     * Returns the customer group.
     *
     * @return Shopware\Models\Customer\Group
     */
    public function getCustomerGroup()
    {
        $key = PlentymarketsConfig::getInstance()->get('DefaultCustomerGroupKey');

        return Shopware()->Models()->getRepository(
            'Shopware\Models\Customer\Group'
        )->findOneBy(['key' => $key]);
    }
}
