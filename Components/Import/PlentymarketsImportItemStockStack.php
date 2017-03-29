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
 * This is a stack of SKUs to retrieve the stocks after the import of the items.
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsImportItemStockStack
{
    /**
     * @var PlentymarketsImportItemStockStack
     */
    protected static $Instance;

    /**
     * @var string|int[]
     */
    protected $stack = [];

    /**
     * Singleton: returns an instance
     *
     * @return PlentymarketsImportItemStockStack
     */
    public static function getInstance()
    {
        if (!self::$Instance instanceof self) {
            self::$Instance = new self();
        }

        return self::$Instance;
    }

    /**
     * Adds the given SKU to the stack
     *
     * @param string|int $sku
     */
    public function add($sku)
    {
        $this->stack[] = $sku;
    }

    /**
     * Retrieves the stocks for the stack
     */
    public function import()
    {
        // Unify
        $this->stack = array_unique($this->stack);

        if (empty($this->stack)) {
            return;
        }

        // Chunkify
        $stacks = array_chunk($this->stack, 100);

        // Reset
        $this->stack = [];

        // Warehouse
        $warehouseId = PlentymarketsConfig::getInstance()->getItemWarehouseID(0);

        // Build the request
        $Request_GetCurrentStocks = new PlentySoapRequest_GetCurrentStocks();
        $Request_GetCurrentStocks->Page = 0;

        $ImportEntityItemStock = PlentymarketsImportEntityItemStock::getInstance();

        foreach ($stacks as $stack) {
            // Reset
            $Request_GetCurrentStocks->Items = [];

            // Add the SKUs
            foreach ($stack as $sku) {
                $RequestObject_GetCurrentStocks = new PlentySoapRequestObject_GetCurrentStocks();
                $RequestObject_GetCurrentStocks->SKU = $sku;
                $Request_GetCurrentStocks->Items[] = $RequestObject_GetCurrentStocks;
            }

            // Log
            PlentymarketsLogger::getInstance()->message('Sync:Item:Stock', 'Fetching ' . count($Request_GetCurrentStocks->Items) . ' stocks');

            // Do the request
            $Response_GetCurrentStocks = PlentymarketsSoapClient::getInstance()->GetCurrentStocks($Request_GetCurrentStocks);

            // Process
            /** @var PlentySoapObject_GetCurrentStocks $CurrentStock */
            foreach ($Response_GetCurrentStocks->CurrentStocks->item as $CurrentStock) {
                // Skip wrong warehouses
                if ($CurrentStock->WarehouseID != $warehouseId) {
                    continue;
                }

                $ImportEntityItemStock->update($CurrentStock);
            }
        }
    }
}
