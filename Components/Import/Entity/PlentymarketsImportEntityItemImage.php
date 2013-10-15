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

require_once PY_SOAP . 'Models/PlentySoapObject/GetItemsImages.php';
require_once PY_SOAP . 'Models/PlentySoapObject/ItemImage.php';
require_once PY_SOAP . 'Models/PlentySoapRequest/GetItemsImages.php';

/**
 * PlentymarketsImportEntityItemImage provides the actual item image import funcionality. Like the other import
 * entities this class is called in PlentymarketsImportController. It is important to deliver at least a plenty item ID or
 * a shopware item ID to the constructor method of this class.
 * The data import takes place based on plentymarkets SOAP-calls.
 *
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsImportEntityItemImage
{

	/**
	 *
	 * @var integer
	 */
	protected $PLENTY_itemId;

	/**
	 *
	 * @var integer
	 */
	protected $SHOPWARE_itemId;

	/**
	 * Constructor method
	 *
	 * @param integer $PLENTY_itemId
	 * @param integer $SHOPWARE_itemId
	 */
	public function __construct($PLENTY_itemId, $SHOPWARE_itemId = null)
	{
		$this->PLENTY_itemId = $PLENTY_itemId;
		if (is_null($SHOPWARE_itemId))
		{
			$this->SHOPWARE_itemId = PlentymarketsMappingController::getItemByPlentyID($PLENTY_itemId);
		}
		else
		{
			$this->SHOPWARE_itemId = $SHOPWARE_itemId;
		}
	}

	/**
	 * Deletes all existing images of the item
	 */
	public function purge()
	{
		$MediaResource = \Shopware\Components\Api\Manager::getResource('Media');
		$ArticleResource = \Shopware\Components\Api\Manager::getResource('Article');

		$article = $ArticleResource->getOne($this->SHOPWARE_itemId);
		foreach ($article['images'] as $image)
		{
			try
			{
				$MediaResource->delete($image['mediaId']);
			}
			catch (Exception $E)
			{
			}
		}

		$ArticleResource->update($this->SHOPWARE_itemId, array(
			'images' => array()
		));
	}

	/**
	 * Retrieves the images from plentymarkets and adds them to the item
	 * @return number
	 */
	public function image()
	{
		$images = array();

		$Request_GetItemsImages = new PlentySoapRequest_GetItemsImages();
		$Request_GetItemsImages->Page = 0;
		$Request_GetItemsImages->SKU = $this->PLENTY_itemId; // string

		do
		{

			// Do the request
			$Response_GetItemsImages = PlentymarketsSoapClient::getInstance()->GetItemsImages($Request_GetItemsImages);
			$Response_GetItemsImages instanceof PlentySoapResponse_GetItemsImages;

			if ($Response_GetItemsImages->Success == false)
			{
				PlentymarketsLogger::getInstance()->error('Sync:Item:Image', 'The images for the plentymarkets item id »' . $this->PLENTY_itemId . '« could not be retrieved', 3200);
				continue;
			}

			foreach ($Response_GetItemsImages->ItemsImages->item as $ImagesImages)
			{
				$ImagesImages instanceof PlentySoapObject_GetItemsImages;

				foreach ($ImagesImages->Images->item as $Image)
				{
					$Image instanceof PlentySoapObject_ItemImage;
					$images[] = array(
						'link' => $Image->ImageURL,
						'position' => $Image->Position,
						'main' => 2
					);
				}
			}
		}

		// Until all pages are received
		while (++$Request_GetItemsImages->Page < $Response_GetItemsImages->Pages);

		// Cleanup
		$this->purge();

		if (count($images))
		{
			// Sort by position to determine the main image
			usort($images, function ($a, $b) {
				if ($a['position'] == $b['position'])
				{
					return 0;
				}
				return ($a['position'] < $b['position']) ? -1 : 1;
			});

			// Set the first one as main image
			$images[0]['main'] = 1;

			//
			$ArticleResource = \Shopware\Components\Api\Manager::getResource('Article');
			$ArticleResource->update($this->SHOPWARE_itemId, array(
				'images' => $images
			));
		}
	}
}
