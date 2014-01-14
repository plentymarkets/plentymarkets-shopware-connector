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

require_once PY_COMPONENTS . 'Import/Entity/Order/PlentymarketsImportEntityItemBundle.php';
require_once PY_SOAP . 'Models/PlentySoapRequest/GetItemBundles.php';

/**
 * Controller of the item bundles
 *
 * Class PlentymarketsImportControllerItemBundle
 */
class PlentymarketsImportControllerItemBundle
{
	/**
	 * Registers the bundle module
	 */
	public function __construct()
	{
		PlentymarketsUtils::registerBundleModules();
	}

	/**
	 * Does the actual import work
	 */
	public function import()
	{
		PlentymarketsLogger::getInstance()->message('Sync:Item:Bundle', 'LastUpdate: ' . date('r', PlentymarketsConfig::getInstance()->getImportItemBundleLastUpdateTimestamp(time())));

		$numberOfBundlesUpdated = 0;
		$timestamp = PlentymarketsConfig::getInstance()->getImportItemBundleLastUpdateTimestamp(1);
		$now = time();

		// Get all bundles
		$Request_GetItemBundles = new PlentySoapRequest_GetItemBundles();
		$Request_GetItemBundles->LastUpdate = $timestamp;
		$Request_GetItemBundles->Page = 0;

		do
		{
			/** @var $Response_GetItemBundles PlentySoapResponse_GetItemBundles */
			$Response_GetItemBundles = PlentymarketsSoapClient::getInstance()->GetItemBundles($Request_GetItemBundles);

			$pages = max($Response_GetItemBundles->Pages, 1);
			PlentymarketsLogger::getInstance()->message('Sync:Item:Bundle', 'Page: ' . ($Response_GetItemBundles->Page + 1) . '/' . $pages);

			foreach ($Response_GetItemBundles->ItemBundles->item as $PlentySoapObject_Bundle)
			{
				$PlentymarketsImportEntityItemBundle = new PlentymarketsImportEntityItemBundle($PlentySoapObject_Bundle);
				try
				{
					$PlentymarketsImportEntityItemBundle->import();
					++$numberOfBundlesUpdated;
				}
				catch (Exception $e)
				{
					PyLog()->error('Sync:Item:Bundle', $e->getMessage());
				}
			}
		} // Until all pages are received
		while (++$Request_GetItemBundles->Page < $Response_GetItemBundles->Pages);

		PlentymarketsConfig::getInstance()->setImportItemBundleLastUpdateTimestamp($now);

		// Log
		if ($numberOfBundlesUpdated == 0)
		{
			PlentymarketsLogger::getInstance()->message('Sync:Item:Bundle', 'No item bundle has been updated or created.');
		}
		else if ($numberOfBundlesUpdated == 1)
		{
			PlentymarketsLogger::getInstance()->message('Sync:Item:Bundle', '1 item bundle has been updated or created.');
		}
		else
		{
			PlentymarketsLogger::getInstance()->message('Sync:Item:Bundle', $numberOfBundlesUpdated . ' item bundles have been updated or created.');
		}
	}
}
