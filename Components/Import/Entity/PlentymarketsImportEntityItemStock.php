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
 * PlentymarketsImportEntityItemStock provides the actual item stock import functionality. Like the other import
 * entities this class is called in PlentymarketsImportController.
 * The data import takes place based on plentymarkets SOAP-calls.
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsImportEntityItemStock
{
    /**
     * @var PlentymarketsImportEntityItemStock
     */
    protected static $Instance;

    /**
     * @var int
     */
    protected $itemWarehousePercentage;

    /**
     * @var int
     */
    protected $numberOfStocksUpdated = 0;

    /**
     * I am the constructor
     */
    protected function __construct()
    {
        $itemWarehousePercentage = PlentymarketsConfig::getInstance()->getItemWarehousePercentage(100);

        if ($itemWarehousePercentage > 100 || $itemWarehousePercentage <= 0) {
            $itemWarehousePercentage = 100;
        }

        $this->itemWarehousePercentage = $itemWarehousePercentage;
    }

    /**
     * I am the singleton method
     *
     * @return PlentymarketsImportEntityItemStock
     */
    public static function getInstance()
    {
        if (!self::$Instance instanceof self) {
            self::$Instance = new self();
        }

        return self::$Instance;
    }

    /**
     * Updates the stock for the given PlentySoapObject_GetCurrentStocks object
     *
     * @param PlentySoapObject_GetCurrentStocks $CurrentStock
     */
    public function update($CurrentStock)
    {
        try {
            // Master item
            if (preg_match('/\d+\-\d+\-0/', $CurrentStock->SKU)) {
                $parts = explode('-', $CurrentStock->SKU);

                $itemId = PlentymarketsMappingController::getItemByPlentyID((int) $parts[0]);
                $Item = Shopware()->Models()->find('Shopware\Models\Article\Article', $itemId);

                // Book
                $this->updateByDetail($Item->getMainDetail(), $CurrentStock->NetStock);
            }

            // Variant
            else {
                $itemDetailId = PlentymarketsMappingController::getItemVariantByPlentyID($CurrentStock->SKU);

                // Book
                $this->updateById($itemDetailId, $CurrentStock->NetStock);
            }
        }

        // Item does not exists
        catch (PlentymarketsMappingExceptionNotExistant $E) {
        }

        // Something went wrong
        catch (Exception $E) {
            PlentymarketsLogger::getInstance()->error('Sync:Item:Stock', 'The stock of the item detail with the id »' . $itemDetailId . '« could not be updated (' . $E->getMessage() . ')', 3510);
        }
    }

    /**
     * Updates the stock for the given item detail id
     *
     * @param int $itemDetailId
     * @param float $stock
     */
    protected function updateById($itemDetailId, $stock)
    {
        // Get the detail
        $Detail = Shopware()->Models()->find('Shopware\Models\Article\Detail', $itemDetailId);

        if (!$Detail instanceof Shopware\Models\Article\Detail) {
            PlentymarketsLogger::getInstance()->error('Sync:Item:Stock', 'The stock of the item detail with the id »' . $itemDetailId . '« could not be updated (detail corrupt)', 3511);
        } else {
            $this->updateByDetail($Detail, $stock);
        }
    }

    /**
     * Updates the stock for the given item detail
     *
     * @param Shopware\Models\Article\Detail $Detail
     * @param float $stock
     */
    protected function updateByDetail(Shopware\Models\Article\Detail $Detail, $stock)
    {
        if ($stock > 0) {
            // At least one
            $stock = max(1, ceil($stock / 100 * $this->itemWarehousePercentage));
        }

        // Remember the last stock (for the log message)
        $previousStock = $Detail->getInStock();
        $diff = $stock - $previousStock;

        // Nothing to to
        if ($previousStock == $stock || $diff == 0) {
            return;
        }

        // Set the stock
        $Detail->setInStock($stock);

        // And save it
        Shopware()->Models()->persist($Detail);
        Shopware()->Models()->flush();

        // Log
        if ($diff > 0) {
            $diff = '+' . $diff;
        }
        PlentymarketsLogger::getInstance()->message('Sync:Item:Stock', 'The stock of the item »' . $Detail->getArticle()->getName() . '« with the number »' . $Detail->getNumber() . '« has been rebooked to ' . $stock . ' (' . $diff . ')');
    }
}
