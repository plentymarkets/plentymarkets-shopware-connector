<?php

namespace ShopwareAdapter\DataPersister\Translation;

use Doctrine\DBAL\Exception\InvalidArgumentException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Shopware\Models\Article\Image as ArticleImage;
use Shopware_Components_Translation;
use ShopwareAdapter\DataProvider\Translation\TranslationDataProviderInterface;
use ShopwareAdapter\ShopwareAdapter;
use SystemConnector\IdentityService\IdentityServiceInterface;
use SystemConnector\IdentityService\Struct\Identity;
use SystemConnector\TransferObject\Category\Category;
use SystemConnector\TransferObject\Language\Language;
use SystemConnector\TransferObject\Media\Media;
use SystemConnector\TransferObject\Product\Image\Image;
use SystemConnector\TransferObject\Product\Product;
use SystemConnector\TransferObject\Product\Property\Property;
use SystemConnector\TransferObject\Product\Property\Value\Value;
use SystemConnector\Translation\TranslationHelperInterface;
use SystemConnector\ValueObject\Attribute\Attribute;
use Zend_Db_Adapter_Exception;

class TranslationDataPersister implements TranslationDataPersisterInterface
{
    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var TranslationDataProviderInterface
     */
    private $dataProvider;

    /**
     * @var TranslationHelperInterface
     */
    private $translationHelper;

    /**
     * @var Shopware_Components_Translation
     */
    private $shopwareTranslationManager;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        IdentityServiceInterface $identityService,
        LoggerInterface $logger,
        TranslationDataProviderInterface $dataProvider,
        TranslationHelperInterface $translationHelper,
        Shopware_Components_Translation $shopwareTranslationManager,
        EntityManagerInterface $entityManager
    ) {
        $this->identityService = $identityService;
        $this->logger = $logger;
        $this->dataProvider = $dataProvider;
        $this->translationHelper = $translationHelper;
        $this->shopwareTranslationManager = $shopwareTranslationManager;
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function writeProductTranslations(Product $product)
    {
        $productIdentity = $this->identityService->findOneBy([
            'objectIdentifier' => $product->getIdentifier(),
            'objectType' => Product::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
        ]);

        if (null === $productIdentity) {
            return;
        }

        foreach ($this->translationHelper->getLanguageIdentifiers($product) as $languageIdentifier) {
            /**
             * @var Product $translatedProduct
             */
            $translatedProduct = $this->translationHelper->translate($languageIdentifier, $product);

            $languageIdentity = $this->identityService->findOneBy([
                'objectIdentifier' => $languageIdentifier,
                'objectType' => Language::TYPE,
                'adapterName' => ShopwareAdapter::NAME,
            ]);

            if (null === $languageIdentity) {
                $this->logger->notice('language not mapped - ' . $languageIdentifier);

                continue;
            }

            $translation = [
                'name' => $translatedProduct->getName(),
                'description' => $translatedProduct->getDescription(),
                'descriptionLong' => $translatedProduct->getLongDescription(),
                'metaTitle' => $translatedProduct->getMetaTitle(),
                'metaDescription' => $translatedProduct->getMetaDescription(),
                'keywords' => $translatedProduct->getMetaKeywords(),
            ];

            foreach ($product->getAttributes() as $attribute) {
                /**
                 * @var Attribute $translatedAttribute
                 */
                $translatedAttribute = $this->translationHelper->translate($languageIdentifier, $attribute);

                $key = '__attribute_plenty_connector' . ucfirst($attribute->getKey());
                $attribute_key = strtolower(preg_replace('/[A-Z]/', '_\\0', lcfirst($key)));
                $translation[$attribute_key] = $translatedAttribute->getValue();
            }

            $this->writeTranslations(
                'article',
                (int) $productIdentity->getAdapterIdentifier(),
                $translation,
                $languageIdentity
            );
        }

        foreach ($product->getProperties() as $property) {
            $this->writeGroupTranslations($property, 'propertyoption');

            foreach ($property->getValues() as $value) {
                $this->writeValueTranslations($value, 'propertyvalue');
            }
        }

        foreach ($product->getVariantConfiguration() as $variantConfiguration) {
            $this->writeGroupTranslations($variantConfiguration, 'configuratorgroup');

            foreach ($variantConfiguration->getValues() as $value) {
                $this->writeValueTranslations($value, 'configuratoroption');
            }
        }

        foreach ($product->getImages() as $articleImage) {
            $this->writeMediaTranslations($articleImage, $productIdentity->getAdapterIdentifier());
        }
    }

    /**
     * @param Category $category
     */
    public function writeCategoryTranslations(Category $category)
    {
        $categoryIdentity = $this->identityService->findOneBy([
            'objectIdentifier' => $category->getIdentifier(),
            'objectType' => Category::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
        ]);

        if (null === $categoryIdentity) {
            return;
        }

        foreach ($this->translationHelper->getLanguageIdentifiers($category) as $languageIdentifier) {
            /**
             * @var Category $translatedCategory
             */
            $translatedCategory = $this->translationHelper->translate($languageIdentifier, $category);

            $languageIdentity = $this->identityService->findOneBy([
                'objectIdentifier' => $languageIdentifier,
                'objectType' => Language::TYPE,
                'adapterName' => ShopwareAdapter::NAME,
            ]);

            if (null === $languageIdentity) {
                $this->logger->notice('language not mapped - ' . $languageIdentifier);

                continue;
            }

            $translation = [
                'description' => $translatedCategory->getName(),
                'metatitle' => $translatedCategory->getMetaTitle(),
                'metakeywords' => $translatedCategory->getMetaKeywords(),
                'metadescription' => $translatedCategory->getMetaDescription(),
                'cmsheadline' => $translatedCategory->getDescription(),
                'cmstext' => $translatedCategory->getLongDescription(),
            ];

            foreach ($category->getAttributes() as $attribute) {
                /**
                 * @var Attribute $translatedAttribute
                 */
                $translatedAttribute = $this->translationHelper->translate($languageIdentifier, $attribute);

                $key = '__attribute_plenty_connector' . ucfirst($attribute->getKey());
                $attribute_key = strtolower(preg_replace('/[A-Z]/', '_\\0', lcfirst($key)));
                $translation[$attribute_key] = $translatedAttribute->getValue();
            }

            $this->writeTranslations(
                'category',
                (int) $categoryIdentity->getAdapterIdentifier(),
                $translation,
                $languageIdentity
            );
        }
    }

    /**
     * @param ArticleImage $image
     *
     * @throws InvalidArgumentException
     */
    public function removeMediaTranslation(ArticleImage $image)
    {
        $this->entityManager->getConnection()->delete(
            's_core_translations',
            ['objectkey' => $image->getId()],
            ['objecttype' => 'articleimage']
        );
    }

    /**
     * @param Property $property
     * @param string   $type
     */
    private function writeGroupTranslations(Property $property, $type)
    {
        $groupModel = null;

        if ($type === 'propertyoption') {
            $groupModel = $this->dataProvider->getPropertyOptionByName($property);
        } elseif ($type === 'configuratorgroup') {
            $groupModel = $this->dataProvider->getConfigurationGroupByName($property);
        }

        if (null === $groupModel) {
            $this->logger->notice('property option not found - ' . $property->getName());

            return;
        }

        foreach ($this->translationHelper->getLanguageIdentifiers($property) as $languageIdentifier) {
            /**
             * @var Property $translatedProperty
             */
            $translatedProperty = $this->translationHelper->translate($languageIdentifier, $property);

            $languageIdentity = $this->identityService->findOneBy([
                'objectIdentifier' => $languageIdentifier,
                'objectType' => Language::TYPE,
                'adapterName' => ShopwareAdapter::NAME,
            ]);

            if (null === $languageIdentity) {
                $this->logger->notice('language not mapped - ' . $languageIdentifier);

                continue;
            }

            if ($type === 'propertyoption') {
                $translation = [
                    'optionName' => $translatedProperty->getName(),
                ];
            } elseif ($type === 'configuratorgroup') {
                $translation = [
                    'name' => $translatedProperty->getName(),
                ];
            }

            if (empty($translation)) {
                continue;
            }

            $this->writeTranslations(
                $type,
                $groupModel->getId(),
                $translation,
                $languageIdentity
            );
        }
    }

    /**
     * @param Value  $value
     * @param string $type
     */
    private function writeValueTranslations(Value $value, $type)
    {
        $valueModel = null;

        if ($type === 'propertyvalue') {
            $valueModel = $this->dataProvider->getPropertyValueByValue($value);
        } elseif ($type === 'configuratoroption') {
            $valueModel = $this->dataProvider->getConfigurationOptionByName($value);
        }

        if (null === $valueModel) {
            $this->logger->notice('property value not found - ' . $value->getValue());

            return;
        }

        foreach ($this->translationHelper->getLanguageIdentifiers($value) as $languageIdentifier) {
            /**
             * @var Value $translatedPropertyValue
             */
            $translatedPropertyValue = $this->translationHelper->translate($languageIdentifier, $value);

            $languageIdentity = $this->identityService->findOneBy([
                'objectIdentifier' => $languageIdentifier,
                'objectType' => Language::TYPE,
                'adapterName' => ShopwareAdapter::NAME,
            ]);

            if (null === $languageIdentity) {
                $this->logger->notice('language not mapped - ' . $languageIdentifier);

                continue;
            }

            if ($type === 'propertyvalue') {
                $translation = [
                    'optionValue' => $translatedPropertyValue->getValue(),
                ];
            } elseif ($type === 'configuratoroption') {
                $translation = [
                    'name' => $translatedPropertyValue->getValue(),
                ];
            }

            if (empty($translation)) {
                continue;
            }

            $this->writeTranslations(
                $type,
                $valueModel->getId(),
                $translation,
                $languageIdentity
            );
        }
    }

    /**
     * @param Image $imageTransferObject
     * @param $articleId
     */
    private function writeMediaTranslations($imageTransferObject, $articleId)
    {
        $mediaIdentity = $this->identityService->findOneBy([
            'objectIdentifier' => $imageTransferObject->getMediaIdentifier(),
            'objectType' => Media::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
        ]);

        if (null === $mediaIdentity) {
            $this->logger->notice('image not mapped - ' . $imageTransferObject->getMediaIdentifier());

            return;
        }

        $articleImage = $this->dataProvider->getArticleImage($mediaIdentity, $articleId);

        if (null === $articleImage) {
            $this->logger->notice('image not found - ' . $mediaIdentity->getObjectIdentifier());

            return;
        }

        foreach ($this->translationHelper->getLanguageIdentifiers($imageTransferObject) as $languageIdentifier) {
            /**
             * @var Image $translatedMedia
             */
            $translatedMedia = $this->translationHelper->translate($languageIdentifier, $imageTransferObject);

            $languageIdentity = $this->identityService->findOneBy([
                'objectIdentifier' => $languageIdentifier,
                'objectType' => Language::TYPE,
                'adapterName' => ShopwareAdapter::NAME,
            ]);

            if (null === $languageIdentity) {
                $this->logger->notice('language not mapped - ' . $languageIdentifier);

                continue;
            }

            $translation = ['description' => $translatedMedia->getName()];

            if (empty($translation)) {
                continue;
            }

            $this->writeTranslations(
                'articleimage',
                $articleImage->getId(),
                $translation,
                $languageIdentity
            );
        }
    }

    /**
     * @param string   $type
     * @param int      $primaryKey
     * @param array    $translation
     * @param Identity $languageIdentity
     *
     * @throws Zend_Db_Adapter_Exception
     */
    private function writeTranslations($type, $primaryKey, array $translation, Identity $languageIdentity)
    {
        $shops = $this->dataProvider->getShopsByLocaleIdentity($languageIdentity);

        foreach ($shops as $shop) {
            $this->shopwareTranslationManager->write(
                $shop->getId(),
                $type,
                $primaryKey,
                $translation
            );
        }
    }
}
