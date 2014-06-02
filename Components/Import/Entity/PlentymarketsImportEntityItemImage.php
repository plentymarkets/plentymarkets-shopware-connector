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
 * PlentymarketsImportEntityItemImage provides the actual item image import functionality. Like the other import
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
	 * @var Shopware\Models\Article\Article
	 */
	protected $SHOPWARE_Article;

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
		$this->SHOPWARE_Article = Shopware()->Models()->find('Shopware\Models\Article\Article', $this->SHOPWARE_itemId);
	}

	/**
	 * Deletes all existing images of the item
	 */
	public function purge()
	{
		/**
		 * @var \Shopware\Components\Api\Resource\Media $MediaResource
		 * @var \Shopware\Components\Api\Resource\Article $ArticleResource
		 */
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
		$plentyId2ShopwareId = array();
		$shopwareId2PlentyPosition = array();

		$Request_GetItemsImages = new PlentySoapRequest_GetItemsImages();
		$Request_GetItemsImages->Page = 0;
		$Request_GetItemsImages->SKU = $this->PLENTY_itemId;

		// Cleanup
		$this->purge();

		/** @var \Shopware\Components\Api\Resource\Media $mediaResource */
		$mediaResource = \Shopware\Components\Api\Manager::getResource('Media');

		do
		{
			/** @var PlentySoapResponse_GetItemsImages $Response_GetItemsImages */
			$Response_GetItemsImages = PlentymarketsSoapClient::getInstance()->GetItemsImages($Request_GetItemsImages);

			if ($Response_GetItemsImages->Success == false)
			{
				PlentymarketsLogger::getInstance()->error('Sync:Item:Image', 'The images for the plentymarkets item id »' . $this->PLENTY_itemId . '« could not be retrieved', 3200);
				continue;
			}

			/**
			 * @var PlentySoapObject_GetItemsImages $ImagesImages
			 * @var PlentySoapObject_ItemImage $Image
			 */
			foreach ($Response_GetItemsImages->ItemsImages->item as $ImagesImages)
			{
				foreach ($ImagesImages->Images->item as $Image)
				{
					// Skip the image if it should not be shown
					if ($Image->Availability != 1 && $Image->Availability != 2)
					{
						continue;
					}

					/** @var Shopware\Models\Media\Media $media */
					$media = $mediaResource->internalCreateMediaByFileLink($Image->ImageURL);

					$image = new \Shopware\Models\Article\Image();
					$image->setMain(2);
					$image->setMedia($media);
					$image->setPath($media->getName());
					$image->setExtension($media->getExtension());
					$image->setDescription($media->getDescription());
					$image->setPosition($Image->Position);

					// Generate the thumbnails
					if (version_compare('4.2', Shopware::VERSION) != 1)
					{
						$manager = Shopware()->Container()->get('thumbnail_manager');
						$manager->createMediaThumbnail($media, array(), true);
					}

					Shopware()->Models()->persist($image);
					Shopware()->Models()->flush();

					$imageId = $image->getId();

					$plentyId2ShopwareId[$Image->ImageID] = $imageId;
					$shopwareId2PlentyPosition[$Image->Position] = $imageId;

					Shopware()->DB()->query('
						UPDATE s_articles_img
							SET articleID = ?
							WHERE id = ?
					', array($this->SHOPWARE_itemId, $imageId));
				}
			}
		} while (++$Request_GetItemsImages->Page < $Response_GetItemsImages->Pages);

		if (!$shopwareId2PlentyPosition)
		{
			return;
		}

		ksort($shopwareId2PlentyPosition);
		$mainImageId = reset($shopwareId2PlentyPosition);

		/** @var Shopware\Models\Article\Image $mainImage */
		$mainImage = Shopware()->Models()->find('Shopware\Models\Article\Image', $mainImageId);
		$mainImage->setMain(1);
		Shopware()->Models()->persist($mainImage);
		Shopware()->Models()->flush();

		// Get the variant images
		$Request_GetItemsVariantImages = new PlentySoapRequest_GetItemsVariantImages();

		$Request_GetItemsVariantImages->Items = array();
		$RequestObject_GetItemsVariantImages = new PlentySoapRequestObject_GetItemsVariantImages();
		$RequestObject_GetItemsVariantImages->ItemID = $this->PLENTY_itemId;
		$Request_GetItemsVariantImages->Items[] = $RequestObject_GetItemsVariantImages;

		/** @var PlentySoapResponse_GetItemsVariantImages $Response_GetItemsVariantImages */
		$Response_GetItemsVariantImages = PlentymarketsSoapClient::getInstance()->GetItemsVariantImages($Request_GetItemsVariantImages);

		/** @var PlentySoapObject_GetItemsVariantImagesImage $GetItemsVariantImagesImage */
		foreach ($Response_GetItemsVariantImages->Images->item as $GetItemsVariantImagesImage)
		{
			try
			{
				$shopwareOptionId = PlentymarketsMappingController::getAttributeOptionByPlentyID($GetItemsVariantImagesImage->AttributeValueID);
				$shopwareOption = Shopware()->Models()->find('Shopware\Models\Article\Configurator\Option', $shopwareOptionId);
			}
			catch (PlentymarketsMappingExceptionNotExistant $e)
			{
				continue;
			}

			if (!isset($plentyId2ShopwareId[$GetItemsVariantImagesImage->ImageID]))
			{
				continue;
			}

			/** @var Shopware\Models\Article\Image $shopwareImage */
			$shopwareImageId = $plentyId2ShopwareId[$GetItemsVariantImagesImage->ImageID];
			$shopwareImage = Shopware()->Models()->find('Shopware\Models\Article\Image', $shopwareImageId);

			$mapping = new Shopware\Models\Article\Image\Mapping();
			$mapping->setImage($shopwareImage);

			$rule = new Shopware\Models\Article\Image\Rule();
			$rule->setMapping($mapping);
			$rule->setOption($shopwareOption);

			$mapping->getRules()->add($rule);
			$shopwareImage->setMappings($mapping);

			Shopware()->Models()->persist($mapping);

			$details = Shopware()->Db()->fetchCol('
				SELECT
						d.id
					FROM s_articles_details d
						INNER JOIN s_article_configurator_option_relations alias16 ON alias16.option_id = ' . $shopwareOptionId . ' AND alias16.article_id = d.id
					WHERE d.articleID = ' . $this->SHOPWARE_itemId . '
			');

			foreach ($details as $detailId)
			{
				// Get the detail object
				$detail = Shopware()->Models()->getReference('Shopware\Models\Article\Detail', $detailId);

				// Create the variant image
				$variantImage = new Shopware\Models\Article\Image();
				$variantImage->setExtension($shopwareImage->getExtension());
				$variantImage->setMain($shopwareImage->getMain());
				$variantImage->setParent($shopwareImage);
				$variantImage->setArticleDetail($detail);

				// And persist it
				Shopware()->Models()->persist($variantImage);
			}
		}

		Shopware()->Models()->flush();
	}
}
