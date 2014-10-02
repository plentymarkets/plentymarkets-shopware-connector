<?php
/**
 * Created by IntelliJ IDEA.
 * User: ioana
 * Date: 29/09/14
 * Time: 11:08
 */

class PlentymarketsTranslation 
{
	/**
	 *
	 * @var PlentymarketsTranslation
	 */
	protected static $Instance;


	/**
	 * I am the singleton method
	 *
	 * @return PlentymarketsTranslation
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
	 * @description Get the current language of the shop with id = shopId
	 * @param int $shopId
	 * @return array
	 */
	public static function getShopMainLanguage($shopId)
	{
		/** @var $shopRepositoryList Shopware\Models\Shop\Repository */
		$shopRepositoryList = Shopware()->Models()->getRepository('Shopware\Models\Shop\Shop');
		
		/** @var $shopRepository Shopware\Models\Shop\Shop */
		$shopRepository = $shopRepositoryList->getActiveById($shopId);
		
		$mainLang = array( 'id' => $shopRepository->getLocale()->getId(), 
							'language' => $shopRepository->getLocale()->getLanguage(),
							'locale' => $shopRepository->getLocale()->getLocale());

		return $mainLang;
	}

	/**
	 * @description Get all active languages of the shop with id = shopId
	 * @param int $shopId
	 * @return array
	 */
	public static function getShopActiveLanguages($shopId)
	{
		
		/** @var $shopRepositoryList Shopware\Models\Shop\Repository */
		$shopRepositoryList = Shopware()->Models()->getRepository('Shopware\Models\Shop\Shop');
		
		// get all language shops of the shop with id = $shopId =>  find all shops where mainId = shopId 
		$languageShops = $shopRepositoryList->findBy(array('mainId' => $shopId));
		
		// array for saving the languages of the shop
		$activeLanguages = array();

		/** @var $languageShop Shopware\Models\Shop\Shop */
		foreach($languageShops as $languageShop)
		{
			$activeLanguages[] = array( 'id' => $languageShop->getLocale()->getId(),  // e.g id = 2
										'language' => $languageShop->getLocale()->getLanguage(), // e.g language = Englisch
										'locale' => $languageShop->getLocale()->getLocale());  // e.g locale = en_GB 
		}
		
		return $activeLanguages;
	}
	
	/**
	 * @description Get all languages from shopware
	 * @return array
	 */
	public static function getAllLanguages()
	{
		/** @var $locales */
		$locales = Shopware()->Models()->getRepository('Shopware\Models\Shop\Locale')->findAll();

		$languages = array();

		/** @var  $locale Shopware\Models\Shop\Locale */
		foreach($locales as $locale)
		{
			$languages[] = array( 'id' => $locale->getId(),
								'language' => $locale->getLanguage(),
								'locale' => $locale->getLocale());
		}
		
		return $languages;
	
	}

	/**
	 * @description Get the translation of the object
	 * @param string $type
	 * @param int $objectId
	 * @param int $langId
	 * @return array
	 */
	public static function getShopwareTranslation($type, $objectId, $langId)
	{
		/** @var $locale Shopware\Models\Translation\Translation */
		$localeRepository = Shopware()->Models()->getRepository('Shopware\Models\Translation\Translation');
		
		try
		{
			$keyData = $localeRepository->findOneBy(array( 	'type' => 'article',
															'key' => $objectId,
															'localeId' => $langId));

			$serializedTranslation = $keyData->getData();
			$translation = unserialize( $serializedTranslation);
			
		}catch(Exception $e)
		{
			$translation = null;
		}
		
		return $translation;
	}

	/**
	 * @description Set the translation for the object 
	 * @param string $type
	 * @param int $objectId
	 * @param int $langId
	 * @param array $data
	 * @return bool
	 */
	public static function setShopwareTranslation($type, $objectId, $langId, $data)
	{
		$success = false;
		
		/** @var $locale Shopware\Models\Translation\Translation */
	//	$localeRepository = Shopware()->Models()->getRepository('Shopware\Models\Translation\Translation')->createQueryBuilder('translation')->getQuery()->getResult(); // get all rows from s_core_translation

		$sql = 'INSERT INTO `s_core_translations` (
				`objecttype`, `objectdata`, `objectkey`, `objectlanguage`
				) VALUES (
				?, ?, ?, ?
				) ON DUPLICATE KEY UPDATE `objectdata`=VALUES(`objectdata`);
				';
		
		Shopware()->Db()->query($sql, array($type, serialize($data), $objectId, $langId));

		return $success;
	}

	
} 