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
 * Imports a item producer
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsImportEntityItemProducer
{
	/**
	 *
	 * @var PlentySoapObject_GetProducers
	 */
	protected $Producer;

	/**
	 * I am the constructor
	 *
	 * @param PlentySoapObject_GetProducers $Producer
	 */
	public function __construct(PlentySoapObject_GetProducers $Producer)
	{
		$this->Producer = $Producer;
	}

	/**
	 * Does the actual import
	 */
	public function import()
	{
		try
		{
			$SHOPWARE_id = PlentymarketsMappingController::getProducerByPlentyID($this->Producer->ProducerID);
			PyLog()->message('Sync:Item:Producer', 'Updating the producer »' . $this->Producer->ProducerName . '«');
		}
		catch (PlentymarketsMappingExceptionNotExistant $E)
		{
			PyLog()->message('Sync:Item:Producer', 'Skipping the producer »' . $this->Producer->ProducerName . '«');
			return;
		}

		$Supplier = Shopware()->Models()->find('Shopware\Models\Article\Supplier', $SHOPWARE_id);
		$Supplier instanceof Shopware\Models\Article\Supplier;

		// Set the new data
		$Supplier->setName($this->Producer->ProducerName);
		Shopware()->Models()->persist($Supplier);
		Shopware()->Models()->flush();
	}
}
