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

require_once PY_COMPONENTS . 'Export/PlentymarketsExportController.php';
require_once PY_COMPONENTS . 'Export/Entity/PlentymarketsExportEntityCustomer.php';

/**
 * The class PlentymarketsExportControllerCustomer handles the item export.
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsExportControllerCustomer
{

	/**
	 * PlentymarketsExportControllerCustomer object data.
	 *
	 * @var PlentymarketsExportControllerCustomer
	 */
	protected static $Instance;

	/**
	 * PlentymarketsConfig object data.
	 *
	 * @var PlentymarketsConfig
	 */
	protected $Config;

	/**
	 *
	 * @var integer
	 */
	protected $sizeOfChunk;

	/**
	 * Prepares config data and checks different conditions like finished mapping.
	 */
	protected function __construct()
	{
		// Config
		$this->Config = PlentymarketsConfig::getInstance();

		// Configure
		$this->configure();
	}

	/**
	 * Sets the current status
	 */
	protected function destruct()
	{
		// Set success
		$this->Config->setCustomerExportTimestampFinished(time());
		$this->Config->setCustomerExportStatus('success');
	}

	/**
	 * If an instance of PlentymarketsExportControllerCustomer exists, it returns this instance.
	 * Else it creates a new instance of PlentymarketsExportController.
	 *
	 * @return PlentymarketsExportControllerCustomer
	 */
	public static function getInstance()
	{
		if (!self::$Instance instanceof self)
		{
			self::$Instance = new self();
		}
		return self::$Instance;
	}

	/**
	 * Runs the actual export of the items
	 */
	public function run()
	{
		// Set running
		$this->Config->setCustomerExportStatus('running');

		// Start timestamp
		$this->Config->setCustomerExportTimestampStart(time());

		// Export
		$this->export();

		// Finish
		$this->destruct();
	}

	/**
	 * Configures the chunk settings
	 */
	protected function configure()
	{
		// Items per chunk
		$this->sizeOfChunk = (integer) PlentymarketsConfig::getInstance()->getInitialExportChunkSize(PlentymarketsExportController::DEFAULT_CHUNK_SIZE);
	}

	/**
	 * Exports images, variants, properties item data and items base to make sure, that the corresponding items data exist.
	 */
	protected function export()
	{
		// Repository
		$Repository = Shopware()->Models()->getRepository('Shopware\Models\Customer\Customer');

		// Chunk configuration
		$chunk = 0;

		do
		{

			PlentymarketsLogger::getInstance()->message('Export:Initial:Customer', 'Chunk: ' . ($chunk + 1));
			$Customers = $Repository->findBy(array(), null, $this->sizeOfChunk, $chunk * $this->sizeOfChunk);

			foreach ($Customers as $Customer)
			{
				$Customer instanceof Shopware\Models\Customer\Customer;

				try
				{
					$PlentymarketsExportEntityItem = new PlentymarketsExportEntityCustomer($Customer);
					$PlentymarketsExportEntityItem->export();
				}
				catch (PlentymarketsExportEntityException $E)
				{
					PlentymarketsLogger::getInstance()->error('Export:Initial:Customer', $E->getMessage(), $E->getCode());
				}
			}

			++$chunk;
		}
		while (!empty($Customers) && count($Customers) == $this->sizeOfChunk);
	}
}
