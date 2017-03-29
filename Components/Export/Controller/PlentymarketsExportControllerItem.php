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
 * The class PlentymarketsExportControllerItem handles the item export.
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsExportControllerItem
{
    /**
     * PlentymarketsConfig object data.
     *
     * @var PlentymarketsConfig
     */
    protected $Config;

    /**
     * @var int
     */
    protected $currentChunk = 0;

    /**
     * @var int
     */
    protected $maxChunks;

    /**
     * @var int
     */
    protected $chunksDone = 0;

    /**
     * @var int
     */
    protected $sizeOfChunk;

    /**
     * @var bool
     */
    protected $toBeContinued = false;

    /**
     * Prepares config data and checks different conditions like finished mapping.
     */
    public function __construct()
    {
        // Config
        $this->Config = PlentymarketsConfig::getInstance();

        $this->configure();
    }

    /**
     * Runs the actual export of the items
     */
    public function run()
    {
        // Export
        $this->export();

        // Finish
        $this->destruct();
    }

    /**
     * Checks whether the export is finshed
     *
     * @return bool
     */
    public function isFinished()
    {
        return !$this->toBeContinued;
    }

    /**
     * Sets the current status
     */
    protected function destruct()
    {
        // Set running
        if (!$this->toBeContinued) {
            // Reset values
            $this->Config->setImportItemLastUpdateTimestamp(0);
            $this->Config->setImportItemPriceLastUpdateTimestamp(0);
            $this->Config->setImportItemStockLastUpdateTimestamp(0);
        } else {
            PlentymarketsLogger::getInstance()->message('Export:Initial:Item', 'Stopping. I will continue with the next run.');
        }
    }

    /**
     * Configures the chunk settings
     */
    protected function configure()
    {
        // Check for a previous chunk
        $lastChunk = $this->Config->getItemExportLastChunk();

        if ($lastChunk > 0) {
            $this->currentChunk = $lastChunk + 1;
        }

        // Max. number of chunks per run
        $this->maxChunks = (int) $this->Config->getInitialExportChunksPerRun(PlentymarketsExportController::DEFAULT_CHUNKS_PER_RUN);

        // Items per chunk
        $this->sizeOfChunk = (int) PlentymarketsConfig::getInstance()->getInitialExportChunkSize(PlentymarketsExportController::DEFAULT_CHUNK_SIZE);
    }

    /**
     * Exports images, variants, properties item data and items base to make sure, that the corresponding items data exist.
     */
    protected function export()
    {
        // Query builder
        $QueryBuilder = Shopware()->Models()->createQueryBuilder();
        $QueryBuilder
            ->select('item.id')
            ->from('Shopware\Models\Article\Article', 'item');

        do {
            // Log the chunk
            PlentymarketsLogger::getInstance()->message('Export:Initial:Item', 'Chunk: ' . ($this->currentChunk + 1));

            // Set Limit and Offset
            $QueryBuilder
                ->setFirstResult($this->currentChunk * $this->sizeOfChunk)
                ->setMaxResults($this->sizeOfChunk);

            // Get the items
            $items = $QueryBuilder->getQuery()->getArrayResult();

            $itemsAlreadyExported = 0;

            foreach ($items as $item) {
                try {
                    // If there is a plenty id for this shopware id,
                    // the item has already been exported to plentymarkets
                    PlentymarketsMappingController::getItemByShopwareID($item['id']);

                    ++$itemsAlreadyExported;

                    // already done
                    continue;
                } catch (PlentymarketsMappingExceptionNotExistant $E) {
                }

                $PlentymarketsExportEntityItem = new PlentymarketsExportEntityItem(
                    Shopware()->Models()->find('Shopware\Models\Article\Article', $item['id'])
                );

                $PlentymarketsExportEntityItem->export();
            }

            // Remember the chunk
            $this->Config->setItemExportLastChunk($this->currentChunk);

            if ($this->maxChunks > 0) {
                // Increase number of chunks if every item has already been exported
                if ($itemsAlreadyExported == $this->sizeOfChunk) {
                    ++$this->maxChunks;
                    PlentymarketsLogger::getInstance()->message(
                        'Export:Initial:Item', 'Increasing number of chunks per run to ' . $this->maxChunks . ' since every item of chunk ' . ($this->currentChunk + 1) . ' has already been exported'
                    );
                }

                // Quit when the maximum number of chunks is reached
                if (++$this->chunksDone >= $this->maxChunks) {
                    $this->toBeContinued = true;
                    break;
                }
            }

            // Next chunk
            ++$this->currentChunk;
        } while (!empty($items) && count($items) == $this->sizeOfChunk);
    }
}
