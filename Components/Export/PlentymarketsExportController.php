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
 * The class PlentymarketsExportController does the actual export for different cronjobs e.g. in the class PlentymarketsCronjobController.
 * It uses the different export entities in /Export/Entity, for example PlentymarketsExportEntityCustomer.
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsExportController
{
    /**
     * @var int
     */
    const DEFAULT_CHUNKS_PER_RUN = -1;

    /**
     * @var int
     */
    const DEFAULT_CHUNK_SIZE = 250;

    /**
     * PlentymarketsExportController object data.
     *
     * @var PlentymarketsExportController
     */
    protected static $Instance;

    /**
     * @var array
     */
    protected static $mapping = [
        'ItemCategory' => 'plenty_mapping_category',
        'ItemAttribute' => [
            'plenty_mapping_attribute_group',
            'plenty_mapping_attribute_option',
        ],
        'ItemProperty' => [
            'plenty_mapping_property',
            'plenty_mapping_property_group',
        ],
        'ItemProducer' => 'plenty_mapping_producer',
        'Item' => [
            'plenty_mapping_item',
            'plenty_mapping_item_variant',
        ],
        'ItemBundle' => 'plenty_mapping_item_bundle',
        'Customer' => 'plenty_mapping_customer_billing_address',
        'ItemCrossSelling' => [],
    ];

    /**
     * PlentymarketsConfig object data.
     *
     * @var PlentymarketsConfig
     */
    protected $Config;

    /**
     * @var PlentymarketsExportStatusController
     */
    protected $StatusController;

    /**
     * Indicates whether an export process is running.
     *
     * @var bool
     */
    protected $isRunning = false;

    /**
     * Indicates whether an export may run or not.
     *
     * @var bool
     */
    protected $mayRun = false;

    /**
     * Prepares config data and checks different conditions like finished mapping.
     */
    protected function __construct()
    {
        $this->Config = PlentymarketsConfig::getInstance();
        $this->StatusController = PlentymarketsExportStatusController::getInstance();

        // Check whether a process is running
        $this->isRunning = (bool) $this->Config->getIsExportRunning(false);

        // If the export is not complete
        if (!$this->isComplete()) {
            // the garbage collector will run
            PlentymarketsGarbageCollector::getInstance()->run(PlentymarketsGarbageCollector::ACTION_MAPPING);
        }

        // Check whether settings and mapping are done
        $this->mayRun = PlentymarketsStatus::getInstance()->mayExport();
    }

    /**
     * The returned value indicates whether the settings and mapping are done.
     *
     * @return bool
     */
    public function isComplete()
    {
        return $this->StatusController->isFinished();
    }

    /**
     * Resets an export status
     *
     * @param string $entity
     * @param bool $resetRunning
     */
    public function reset($entity, $resetRunning = true)
    {
        // Reset running status
        if ($resetRunning) {
            $this->Config->setExportEntityPending(false);
            $this->Config->setIsExportRunning(0);
        }

        $this->StatusController->getEntity($entity)->reset();
    }

    /**
     * Erases an export status and the mapping
     *
     * @param string $entity
     */
    public function erase($entity)
    {
        // Erase
        $this->StatusController->getEntity($entity)->erase();

        // Delete Mapping
        foreach ((array) self::$mapping[$entity] as $tableName) {
            try {
                Shopware()->Db()->delete($tableName);
                PlentymarketsLogger::getInstance()->message('Export:Initial:' . $entity, 'Truncated table ' . $tableName);
            } catch (Exception $E) {
            }
        }
    }

    /**
     * This method checks different export states like if an other export is running at the moment, or
     * an export has already announced. In case of complied conditions the actual export gets the state "pending".
     *
     * @param string $entity
     *
     * @throws PlentymarketsExportException
     */
    public function announce($entity)
    {
        if ($this->isRunning == true) {
            throw new PlentymarketsExportException('Another export is running at this very moment', 2510);
        }

        // Check whether another export is waiting to be executed
        $waiting = $this->Config->getExportEntityPending(false);
        if ($waiting == $entity) {
            throw new PlentymarketsExportException('The export has already been announced', 2530);
        }

        if ($waiting != false) {
            throw new PlentymarketsExportException('Another export is waiting to be carried out', 2540);
        }

        // Check whether settings and mapping is complete
        if ($this->mayRun == false) {
            throw new PlentymarketsExportException('Either the mapping or the settings is not finished or the data integrity is not valid', 2520);
        }

        // Check whether or not the order is correct
        if (!$this->StatusController->mayAnnounceEntity($entity)) {
            throw new PlentymarketsExportException('The announcement could not be performed right now', 2550);
        }

        // Set the pending entity
        $this->Config->setExportEntityPending($entity);

        // Erase the timestamp of the latest export call
        $this->Config->eraseInitialExportLastCallTimestamp();

        // Announce
        $this->StatusController->getEntity($entity)->announce();
    }

    /**
     * Starts the actual pending export.
     *
     * @throws PlentymarketsExportException
     */
    public function export()
    {
        if ($this->isRunning == true) {
            throw new PlentymarketsExportException('Another export is running at this very moment', 2510);
        }

        // Check whether settings and mapping is complete
        if ($this->mayRun == false) {
            throw new PlentymarketsExportException('Either the mapping or the settings is not finished or the data integrity is not valid', 2520);
        }

        // Get the pending entity
        $entity = $this->Config->getExportEntityPending(false);

        // No exception.. or the log will ne spammed
        if ($entity == false) {
            return;
        }

        // Set the running flag and delete the last call timestmap and the pending entity
        $this->Config->setIsExportRunning(1);
        $this->Config->eraseExportEntityPending();
        $this->Config->eraseInitialExportLastCallTimestamp();

        // Configure the SOAP client to log the timestamp of the calls from now on
        PlentymarketsSoapClient::getInstance()->setTimestampConfigKey('InitialExportLastCallTimestamp');

        // Log the start
        PlentymarketsLogger::getInstance()->message('Export:Initial:' . $entity, 'Starting');

        // Get the entity status object
        $Status = $this->StatusController->getEntity($entity);

        // Set running
        $Status->setStatus(PlentymarketsExportStatus::STATUS_RUNNING);

        // Set the start timestamp if that hasn't already been done
        if ((int) $Status->getStart() <= 0) {
            $Status->setStart(time());
        }

        try {
            // Get the controller
            $class = sprintf('PlentymarketsExportController%s', $entity);

            // and run it
            $Instance = new $class();
            $Instance->run();

            // Log that we are done
            PlentymarketsLogger::getInstance()->message('Export:Initial:' . $entity, 'Done!');

            // If the export is finished
            if ($Instance->isFinished()) {
                // set the success status and the finished timestamp
                $Status->setStatus(PlentymarketsExportStatus::STATUS_SUCCESS);
                $Status->setFinished(time());
            } else {
                // otherwise re-announce the entity for the next run
                $this->Config->setExportEntityPending($Status->getName());
                $Status->setStatus(PlentymarketsExportStatus::STATUS_PENDING);
            }
        }

        // On error
        catch (PlentymarketsExportException $E) {
            // Log and save the error
            PlentymarketsLogger::getInstance()->error('Export:Initial:' . $entity, $E->getMessage(), $E->getCode());
            $Status->setError($E->getMessage());
        }

        // Reconfigure the soap client
        PlentymarketsSoapClient::getInstance()->setTimestampConfigKey(null);

        // Erase the timestamp of the latest export call
        $this->Config->eraseInitialExportLastCallTimestamp();

        // Reset the running flag
        $this->Config->setIsExportRunning(0);
    }

    /**
     * If an instance of PlentymarketsExportController exists, it returns this instance.
     * Else it creates a new instance of PlentymarketsExportController.
     *
     * @return PlentymarketsExportController
     */
    public static function getInstance()
    {
        if (!self::$Instance instanceof self) {
            self::$Instance = new self();
        }

        return self::$Instance;
    }
}
