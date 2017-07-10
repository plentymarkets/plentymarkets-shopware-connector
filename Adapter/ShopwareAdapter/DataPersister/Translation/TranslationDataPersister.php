<?php

namespace ShopwareAdapter\DataPersister\Translation;

use Doctrine\ORM\EntityManagerInterface;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\Language\Language;
use PlentyConnector\Connector\TransferObject\Product\Product;
use PlentyConnector\Connector\TransferObject\Product\Property\Property;
use PlentyConnector\Connector\TransferObject\Product\Property\Value\Value;
use PlentyConnector\Connector\Translation\TranslationHelperInterface;
use PlentyConnector\Connector\ValueObject\Attribute\Attribute;
use Psr\Log\LoggerInterface;
use Shopware\Components\Model\ModelRepository;
use Shopware\Models\Property\Option as PropertyGroupModel;
use Shopware\Models\Property\Repository as PropertyGroupRepository;
use Shopware\Models\Property\Value as PropertyValueModel;
use Shopware\Models\Shop\Repository as ShopRepository;
use Shopware\Models\Shop\Shop as ShopModel;
use Shopware_Components_Translation;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class TranslationDataPersister
 */
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
     * @var ShopRepository
     */
    private $shopRepository;

    /**
     * @var PropertyGroupRepository
     */
    private $propertyGroupRepository;

    /**
     * @var ModelRepository
     */
    private $propertyValueRepository;

    /**
     * @var TranslationHelperInterface
     */
    private $translationHelper;

    /**
     * @var Shopware_Components_Translation
     */
    private $translationManager;

    /**
     * TranslationHelper constructor.
     *
     * @param IdentityServiceInterface $identityService
     * @param LoggerInterface $logger
     * @param EntityManagerInterface $entityManager
     * @param TranslationHelperInterface $translationHelper
     * @param Shopware_Components_Translation $translationManager
     */
    public function __construct(
        IdentityServiceInterface $identityService,
        LoggerInterface $logger,
        EntityManagerInterface $entityManager,
        TranslationHelperInterface $translationHelper,
        Shopware_Components_Translation $translationManager
    ) {
        $this->identityService = $identityService;
        $this->logger = $logger;
        $this->shopRepository = $entityManager->getRepository(ShopModel::class);
        $this->propertyGroupRepository = $entityManager->getRepository(PropertyGroupModel::class);
        $this->propertyValueRepository = $entityManager->getRepository(PropertyValueModel::class);
        $this->translationHelper = $translationHelper;
        $this->translationManager = $translationManager;
    }

    /**
     * @param Value $value
     */
    private function writePropertyValueTranslations(Value $value)
    {
        $propertyValueModel = $this->propertyValueRepository->findOneBy([
            'value' => $value->getValue(),
        ]);

        if (null === $propertyValueModel) {
            $this->logger->notice('property value not found - ' . $value->getValue());

            return;
        }

        foreach ($this->translationHelper->getLanguageIdentifiers($value) as $languageIdentifier) {
            /**
             * @var Value $translatedPropertyValue
             */
            $translatedPropertyValue = $this->translationHelper->translate($languageIdentifier, $value);

            $languageIdentity = $this->identityService->findOneBy([
                'adapterName' => ShopwareAdapter::NAME,
                'objectType' => Language::TYPE,
                'objectIdentifier' => $languageIdentifier,
            ]);

            if (null === $languageIdentity) {
                $this->logger->notice('langauge not mapped - ' . $languageIdentifier);

                continue;
            }

            $translation = [
                'languageIdentifier' => $languageIdentity->getAdapterIdentifier(),
                'optionValue' => $translatedPropertyValue->getValue(),
            ];

            $this->writeTranslations('propertyvalue', (int) $propertyValueModel->getId(), $translation);
        }
    }

    /**
     * @param Property $property
     */
    private function writePropertyGroupTranslations(Property $property)
    {
        $propertyGroupModel = $this->propertyGroupRepository->findOneBy([
            'name' => $property->getName(),
        ]);

        if (null === $propertyGroupModel) {
            $this->logger->notice('property group not found - ' . $property->getName());

            return;
        }

        foreach ($this->translationHelper->getLanguageIdentifiers($property) as $languageIdentifier) {
            /**
             * @var Property $translatedProperty
             */
            $translatedProperty = $this->translationHelper->translate($languageIdentifier, $property);

            $languageIdentity = $this->identityService->findOneBy([
                'adapterName' => ShopwareAdapter::NAME,
                'objectType' => Language::TYPE,
                'objectIdentifier' => $languageIdentifier,
            ]);

            if (null === $languageIdentity) {
                $this->logger->notice('langauge not mapped - ' . $languageIdentifier);

                continue;
            }

            $translation = [
                'languageIdentifier' => $languageIdentity->getAdapterIdentifier(),
                'optionName' => $translatedProperty->getName(),
            ];

            $this->writeTranslations('propertyoption', (int) $propertyGroupModel->getId(), $translation);
        }
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

        foreach ($this->translationHelper->getLanguageIdentifiers($product) as $languageIdentifier) {
            /**
             * @var Product $translatedProduct
             */
            $translatedProduct = $this->translationHelper->translate($languageIdentifier, $product);

            $languageIdentity = $this->identityService->findOneBy([
                'adapterName' => ShopwareAdapter::NAME,
                'objectType' => Language::TYPE,
                'objectIdentifier' => $languageIdentifier,
            ]);

            if (null === $languageIdentity) {
                $this->logger->notice('langauge not mapped - ' . $languageIdentifier);

                continue;
            }

            $translation = [
                'languageIdentifier' => $languageIdentity->getAdapterIdentifier(),
                'name' => $translatedProduct->getName(),
                'description' => $translatedProduct->getDescription(),
                'descriptionLong' => $translatedProduct->getLongDescription(),
                'keywords' => $translatedProduct->getMetaKeywords(),
            ];

            foreach ($product->getAttributes() as $attribute) {
                /**
                 * @var Attribute $translatedAttribute
                 */
                $translatedAttribute = $this->translationHelper->translate($languageIdentifier, $attribute);

                $key = 'plentyConnector' . ucfirst($attribute->getKey());
                $translation[$key] = $translatedAttribute->getValue();
            }

            $this->writeTranslations('article', $productIdentity->getAdapterIdentifier(), $translation);
        }

        foreach ($product->getProperties() as $property) {
            $this->writePropertyGroupTranslations($property);

            foreach ($property->getValues() as $value) {
                $this->writePropertyValueTranslations($value);
            }
        }
    }

    /**
     * @param string $type
     * @param int $primaryKey
     * @param array $translation
     */
    private function writeTranslations($type, $primaryKey, array $translation)
    {
        $shops = $this->shopRepository->findBy([
            'locale' => $translation['languageIdentifier'],
        ]);

        foreach ($shops as $shop) {
            $this->translationManager->write($shop->getId(), $type, $primaryKey, $translation);
        }
    }
}
