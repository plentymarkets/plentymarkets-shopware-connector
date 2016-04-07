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
				PyLog()->error('Sync:Item:Image', 'The media resource with the id »' . $image['mediaId'] . '« of the item image »' . $image['description'] . '« could not be deleted (' . $E->getMessage() . ')');
			}
		}

		$ArticleResource->update($this->SHOPWARE_itemId, array(
			'images' => array()
		));
	}

	/**
	 * @param $shopware_ImageID int
	 * @param $plenty_ImageNames PlentySoapResponse_ObjectGetItemImageName[]
	 * @param $shopware_storeID int
	 */
	private function importImageTitleTranslation($shopware_ImageID, $plenty_ImageNames, $shopware_storeID)
	 {
		 // get all active languages of the main shop
		 $activeLanguages = PlentymarketsTranslation::getShopActiveLanguages($shopware_storeID);

		 foreach($activeLanguages as $localeId => $language)
		 {
			 /** @var $plentyImageName  PlentySoapResponse_ObjectGetItemImageName */
			 foreach($plenty_ImageNames as $plentyImageName)
			 {
				 if(!is_null($plentyImageName->Name) && strlen($plentyImageName->Name) > 0)
				 {
					 // search the language shop with the language equal as the image name language
					 if(PlentymarketsTranslation::getPlentyLocaleFormat($language['locale']) == $plentyImageName->Lang)
					 {
						 $shopId = null;

						 // if the founded shop is a language shop 
						 if(!is_null($language['mainShopId']))
						 {
							 $shopId = PlentymarketsTranslation::getLanguageShopID($localeId, $language['mainShopId']);

						 }
						 elseif(PlentymarketsTranslation::getPlentyLocaleFormat($language['locale']) != 'de')
						 {
							 // set the imagae title translation for the main shop that has the main language other as German
							 $shopId = $shopware_storeID;
						 }

						 // if the language shop was found / set , save the image title translation for this language shop
						 if(!is_null($shopId))
						 {
							 // save the translation of the plenty image title
							 $image_data = array('description' => $plentyImageName->Name);

							 PlentymarketsTranslation::setShopwareTranslation('articleimage', $shopware_ImageID , $shopId, $image_data);
						 }
					 }
				}				 
			 }	
		 }
				 
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
			
			/** @var $Image PlentySoapResponse_ObjectItemImage */
			foreach ($Response_GetItemsImages->ItemsImages->item as $Image)
			{
				$shopwareStoreIds = array();
					
				/** @var $reference PlentySoapResponse_ObjectGetItemImageReference */
				foreach ($Image->References->item as $reference)
				{
					if(strtolower($reference->ReferenceType) == 'mandant')
					{
						try
						{
							if(PlentymarketsMappingController::getShopByPlentyID($reference->ReferenceValue) > 0)
							{
								$shopwareStoreId = PlentymarketsMappingController::getShopByPlentyID($reference->ReferenceValue);
							}		
						}
						catch (PlentymarketsMappingExceptionNotExistant $E)
						{
							continue;
						}
						if(isset($shopwareStoreId))
						{
							$shopwareStoreIds[] = $shopwareStoreId;
						}
					}
				}
				
				// Skip the image if it should not be shown
				if(empty($shopwareStoreIds))
				{
					continue;
				}
				else
				{
					/** @var Shopware\Models\Media\Media $media */
					$media = $mediaResource->internalCreateMediaByFileLink($Image->ImageURL);

					$image = new \Shopware\Models\Article\Image();
					$image->setMain(2);
					$image->setMedia($media);
					$image->setPath($media->getName());
					$image->setExtension($media->getExtension());

					// get the main language of the shop
					//$mainLangData = array_values(PlentymarketsTranslation::getInstance()->getShopMainLanguage($shopwareStoreId));
					//$mainLang = PlentymarketsTranslation::getInstance()->getPlentyLocaleFormat($mainLangData[0]['locale']);

					/** @var $imageName PlentySoapResponse_ObjectGetItemImageName */
					foreach($Image->Names->item as $imageName)
					{
						if( $imageName->Lang == 'de')
						{
							// set the image title in German
							$image->setDescription($imageName->Name);
						}
					}

					if(!is_null(PlentymarketsConfig::getInstance()->getItemImageAltAttributeID()) &&
						PlentymarketsConfig::getInstance()->getItemImageAltAttributeID() > 0 &&
						PlentymarketsConfig::getInstance()->getItemImageAltAttributeID() <= 3)   // attribute1, attribute2 or attribute3
					{
						// get the attribute number for alternative text from connector's settings
						$plenty_attributeID = PlentymarketsConfig::getInstance()->getItemImageAltAttributeID();
						// set the value for the attribute number 
						$attribute = new \Shopware\Models\Attribute\ArticleImage();
						$attribute->{setAttribute.($plenty_attributeID)}($Image->Names->item[0]->AlternativeText);
						$image->setAttribute($attribute);
					}

					$image->setPosition($Image->Position);

					// Generate the thumbnails
					if (version_compare(Shopware::VERSION, '4.2') != 1)
					{
						PlentymarketsImportItemImageThumbnailController::getInstance()->addMediaResource($media);
					}

					Shopware()->Models()->persist($image);
					Shopware()->Models()->flush();

					$imageId = $image->getId();

					foreach($shopwareStoreIds as $shopwareStoreId) 
					{
						// import the image title translations for all active shops of the image
					 	$this->importImageTitleTranslation($imageId, $Image->Names->item, $shopwareStoreId);
					}

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
		
		// Delete all variant image mappings of the item
		$ArticleResource = \Shopware\Components\Api\Manager::getResource('Article');
		$article = $ArticleResource->getOne($this->SHOPWARE_itemId);
		
		// Add the main detail
		$article['details'][] = $article['mainDetail'];
		
		foreach($article['details'] as $detail)
		{
			Shopware()->Db()->query("DELETE FROM `s_articles_img` WHERE article_detail_id = ?", array($detail['id']));
		}

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
