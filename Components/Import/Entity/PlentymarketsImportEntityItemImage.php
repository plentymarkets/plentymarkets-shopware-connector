<?php
require_once __DIR__ . '/../../Soap/Models/PlentySoapObject/GetItemsImages.php';
require_once __DIR__ . '/../../Soap/Models/PlentySoapObject/ItemImage.php';
require_once __DIR__ . '/../../Soap/Models/PlentySoapRequest/GetItemsImages.php';

/**
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
				PlentymarketsLogger::getInstance()->error('Sync:Item:Image', 'Got negative success from GetItemsImages for plentymarktes itemId ' . $this->PLENTY_itemId);
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
						'position' => $Image->Position
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
			//
// 			$ArticleResource = \Shopware\Components\Api\Manager::getResource('Article');
// 			$ArticleResource->update($this->SHOPWARE_itemId, array(
// 				'images' => $images
// 			));
		}
	}
}
