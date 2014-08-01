<?php

/**
 * @author Daniel Bächtle <daniel.baechtle@plentymarkets.com>
 */
class PlentymarketsImportItemImageThumbnailController
{
	/**
	 *
	 * @var PlentymarketsImportItemImageThumbnailController
	 */
	protected static $instance;

	/**
	 * @var \Shopware\Models\Media\Media[]
	 */
	protected $media = array();

	/**
	 * Singleton: returns an instance
	 *
	 * @return PlentymarketsImportItemImageThumbnailController
	 */
	public static function getInstance()
	{
		if (!self::$instance instanceof self)
		{
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Adds another media resource
	 *
	 * @param \Shopware\Models\Media\Media $media
	 */
	public function addMediaResource(Shopware\Models\Media\Media $media)
	{
		$this->media[] = $media;
	}

	/**
	 * generate the thumbnails
	 */
	public function generate()
	{
		if (!count($this->media))
		{
			return;
		}

		$manager = Shopware()->Container()->get('thumbnail_manager');
		PyLog()->message('Sync:Item:Image:Thumbnail', 'Starting to generate thumbnails for ' . count($this->media) . ' media resources');
		while ($media = array_pop($this->media))
		{
			$manager->createMediaThumbnail($media, array(), true);
		}
		PyLog()->message('Sync:Item:Image:Thumbnail', 'Finished generating thumbnails');
	}
} 
