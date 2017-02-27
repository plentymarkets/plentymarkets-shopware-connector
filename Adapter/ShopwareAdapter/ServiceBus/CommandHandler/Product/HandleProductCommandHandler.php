<?php

namespace ShopwareAdapter\ServiceBus\CommandHandler\Product;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\ServiceBus\Command\CommandInterface;
use PlentyConnector\Connector\ServiceBus\Command\HandleCommandInterface;
use PlentyConnector\Connector\ServiceBus\Command\Product\HandleProductCommand;
use PlentyConnector\Connector\ServiceBus\CommandHandler\CommandHandlerInterface;
use PlentyConnector\Connector\TransferObject\Category\Category;
use PlentyConnector\Connector\TransferObject\CustomerGroup\CustomerGroup;
use PlentyConnector\Connector\TransferObject\Language\Language;
use PlentyConnector\Connector\TransferObject\Manufacturer\Manufacturer;
use PlentyConnector\Connector\TransferObject\Media\Media;
use PlentyConnector\Connector\TransferObject\Product\LinkedProduct\LinkedProduct;
use PlentyConnector\Connector\TransferObject\Product\Product;
use PlentyConnector\Connector\TransferObject\Product\Variation\Variation;
use PlentyConnector\Connector\TransferObject\ShippingProfile\ShippingProfile;
use PlentyConnector\Connector\TransferObject\Unit\Unit;
use PlentyConnector\Connector\TransferObject\VatRate\VatRate;
use PlentyConnector\Connector\Translation\TranslationHelperInterface;
use PlentyConnector\Connector\ValueObject\Attribute\Attribute;
use PlentymarketsAdapter\PlentymarketsAdapter;
use Psr\Log\LoggerInterface;
use Shopware\Bundle\AttributeBundle\Service\CrudService;
use Shopware\Bundle\AttributeBundle\Service\DataPersister;
use Shopware\Bundle\AttributeBundle\Service\TypeMapping;
use Shopware\Components\Api\Manager;
use Shopware\Components\Api\Resource\Article;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Customer\Group;
use Shopware\Models\Shop\Shop;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class HandleProductCommandHandler.
 */
class HandleProductCommandHandler implements CommandHandlerInterface
{
    /**
     * HandleProductCommandHandler constructor.
     */
    public function __construct()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function supports(CommandInterface $command)
    {
        return $command instanceof HandleProductCommand &&
            $command->getAdapterName() === ShopwareAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function handle(CommandInterface $command)
    {
        /**
         * @var HandleCommandInterface $command
         * @var Product $product
         */
        $product = $command->getTransferObject();

        /**
         * @var ModelManager $entityManager
         */
        $entityManager = Shopware()->Container()->get('models');

        /**
         * @var IdentityServiceInterface $identityService
         */
        $identityService = Shopware()->Container()->get('plenty_connector.identity_service');

        $identity = $identityService->findOneBy([
            'objectIdentifier' => $product->getIdentifier(),
            'objectType' => Product::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
        ]);

        $vatIdentity = $identityService->findOneBy([
            'adapterName' => ShopwareAdapter::NAME,
            'objectType' => VatRate::TYPE,
            'objectIdentifier' => $product->getVatRateIdentifier(),
        ]);

        if (null === $vatIdentity) {
            /**
             * @var LoggerInterface $logger
             */
            $logger = Shopware()->Container()->get('plenty_connector.logger');
            $logger->notice('vat rate not mapped', ['command' => $command]);

            return false;
        }

        $manufacturerIdentity = $identityService->findOneBy([
            'adapterName' => ShopwareAdapter::NAME,
            'objectType' => Manufacturer::TYPE,
            'objectIdentifier' => $product->getManufacturerIdentifier(),
        ]);

        if (null === $manufacturerIdentity) {
            /**
             * @var LoggerInterface $logger
             */
            $logger = Shopware()->Container()->get('plenty_connector.logger');
            $logger->notice('manufacturer is missing', ['command' => $command]);
        }

        $images = [];

        foreach ($product->getImageIdentifiers() as $imageIdentifier) {
            $imageIdentity = $identityService->findOneBy([
                'objectIdentifier' => $imageIdentifier,
                'objectType' => Media::TYPE,
                'adapterName' => ShopwareAdapter::NAME,
            ]);

            if (null === $imageIdentity) {
                /**
                 * @var LoggerInterface $logger
                 */
                $logger = Shopware()->Container()->get('plenty_connector.logger');
                $logger->notice('image is missing', ['command' => $command]);

                return false;
            }

            $images[] = [
                'mediaId' => $imageIdentity->getAdapterIdentifier(),
            ];
        }

        $categories = [];
        foreach ($product->getCategoryIdentifiers() as $categoryIdentifier) {
            $categoryIdentity = $identityService->findOneBy([
                'objectIdentifier' => $categoryIdentifier,
                'objectType' => Category::TYPE,
                'adapterName' => ShopwareAdapter::NAME,
            ]);

            if (null === $categoryIdentity) {
                /**
                 * @var LoggerInterface $logger
                 */
                $logger = Shopware()->Container()->get('plenty_connector.logger');
                $logger->notice('category is missing', ['command' => $command]);

                return false;
            }

            $categories[] = [
                'id' => $categoryIdentity->getAdapterIdentifier(),
            ];
        }

        $categoryRepository = $entityManager->getRepository(\Shopware\Models\Category\Category::class);
        $shopRepository = $entityManager->getRepository(Shop::class);

        $seoCategories = [];
        foreach ($product->getDefaultCategoryIdentifiers() as $categoryIdentifier) {
            $categoryIdentity = $identityService->findOneBy([
                'objectIdentifier' => $categoryIdentifier,
                'objectType' => Category::TYPE,
                'adapterName' => ShopwareAdapter::NAME,
            ]);

            if (null === $categoryIdentity) {
                /**
                 * @var LoggerInterface $logger
                 */
                $logger = Shopware()->Container()->get('plenty_connector.logger');
                $logger->notice('seo category is missing', ['command' => $command]);

                return false;
            }

            $category = $categoryRepository->find($categoryIdentity->getAdapterIdentifier());
            $parents = array_reverse(array_filter(explode('|', $category->getPath())));

            $shops = $shopRepository->findBy([
                'categoryId' => array_shift($parents),
            ]);

            foreach ($shops as $shop) {
                $seoCategories[] = [
                    'categoryId' => $categoryIdentity->getAdapterIdentifier(),
                    'shopId' => $shop->getId(),
                ];
            }
        }

        /**
         * @var CrudService $attributeService
         */
        $attributeService = Shopware()->Container()->get('shopware_attribute.crud_service');

        $attributes = [];
        foreach ($product->getAttributes() as $attribute) {
            $attributeConfig = $attributeService->get('s_articles_attributes',
                'plentyconnector_' . $attribute->getKey());

            if (!$attributeConfig) {
                $attributeService->update(
                    's_articles_attributes',
                    'plentyconnector_' . $attribute->getKey(),
                    TypeMapping::TYPE_STRING,
                    [
                        'label' => 'PlentyConnector ' . $attribute->getKey(),
                        'displayInBackend' => true,
                        'translatable' => true,
                        'custom' => true,
                    ]
                );

                // throw exception to restart import run
            }

            $entityManager->generateAttributeModels(['s_articles_attributes']);

            $attributes['plentyconnector_' . $attribute->getKey()] = $attribute->getValue();
        }

        foreach ($product->getShippingProfileIdentifiers() as $identifier) {
            $profileIdentity = $identityService->findOneBy([
                'adapterName' => ShopwareAdapter::NAME,
                'objectType' => ShippingProfile::TYPE,
                'objectIdentifier' => $identifier,
            ]);

            if (null === $profileIdentity) {
                /**
                 * @var LoggerInterface $logger
                 */
                $logger = Shopware()->Container()->get('plenty_connector.logger');
                $logger->notice('shipping profile not mapped', ['command' => $command]);

                continue;
            }

            $columnName = 'plentyconnector_shippingprofile' . $profileIdentity->getAdapterIdentifier();
            $attributeConfig = $attributeService->get('s_articles_attributes', $columnName);

            if (!$attributeConfig) {
                $attributeService->update(
                    's_articles_attributes',
                    $columnName,
                    TypeMapping::TYPE_BOOLEAN,
                    [
                        'label' => 'PlentyConnector ShippingProfile ' . $profileIdentity->getAdapterIdentifier(),
                        'displayInBackend' => true,
                        'custom' => true,
                        'defaultValue' => 0,
                    ]
                );

                $entityManager->generateAttributeModels(['s_articles_attributes']);
            }

            $attributes['plentyconnector_shippingProfile' . $profileIdentity->getAdapterIdentifier()] = 1;
        }

        /**
         * @var TranslationHelperInterface $translationHelper
         */
        $translationHelper = Shopware()->Container()->get('plenty_connector.translation_helper');

        $translations = [];
        foreach ($translationHelper->getLanguageIdentifiers($product) as $languageIdentifier) {
            /**
             * @var Product $translatedProduct
             */
            $translatedProduct = $translationHelper->translate($languageIdentifier, $product);

            $languageIdentity = $identityService->findOneBy([
                'adapterName' => ShopwareAdapter::NAME,
                'objectType' => Language::TYPE,
                'objectIdentifier' => $languageIdentifier,
            ]);

            if (null === $languageIdentity) {
                /**
                 * @var LoggerInterface $logger
                 */
                $logger = Shopware()->Container()->get('plenty_connector.logger');
                $logger->notice('langauge not mapped', ['command' => $command]);

                continue;
            }

            $shops = $shopRepository->findBy([
                'locale' => $languageIdentity->getAdapterIdentifier(),
            ]);

            $translation = [
                'name' => $translatedProduct->getName(),
                'description' => $translatedProduct->getDescription(),
                'descriptionLong' => $translatedProduct->getLongDescription(),
                'keywords' => $translatedProduct->getMetaKeywords(),
            ];

            foreach ($product->getAttributes() as $attribute) {
                /**
                 * @var Attribute $translatedAttribute
                 */
                $translatedAttribute = $translationHelper->translate($languageIdentifier, $attribute);

                $key = '__attribute_plentyconnector' . ucfirst($translatedAttribute->getKey());
                $translation[$key] = $translatedAttribute->getValue();
            }

            foreach ($shops as $shop) {
                $translation['shopId'] = $shop->getId();

                $translations[] = $translation;
            }
        }

        $variations = [];
        foreach ($product->getVariations() as $variation) {
            if (empty($variation->getPrices())) {
                continue;
            }

            $variations[] = $this->getVariationData($variation, $product);
        }

        $mainDetail = array_shift($variations);

        $params = [
            //'priceGroupId' => 0,
            //'filterGroupId' => 0,
            //'pseudoSales => 0,
            //'highlight' => false,
            //'priceGroupActive => 0,
            //'crossBundleLook' => true,
            //'mode' => ?
            //'template'
            //'availableFrom'
            //'availableTo'
            'name' => $product->getName(),
            'description' => $product->getDescription(),
            'descriptionLong' => $product->getLongDescription(),
            'categories' => $categories,
            'seoCategories' => $seoCategories,
            'taxId' => $vatIdentity->getAdapterIdentifier(),
            'lastStock' => $product->getLimitedStock(),
            'notification' => true,
            'mainDetail' => $mainDetail,
            'active' => $product->getActive(),
            'images' => $images,
            'similar' => $this->getLinkedProducts($product, LinkedProduct::TYPE_SIMILAR),
            'related' => $this->getLinkedProducts($product, LinkedProduct::TYPE_ACCESSORY),
            'metaTitle' => $product->getMetaTitle(),
            'keywords' => $product->getMetaKeywords(),
            '__options_categories' => ['replace' => true],
            '__options_similar' => ['replace' => true],
            '__options_prices' => ['replace' => true],
        ];

        if (null !== $manufacturerIdentity) {
            $params['supplierId'] = $manufacturerIdentity->getAdapterIdentifier();
        }

        $configuratorSet = $this->getConfiguratorSet($product);
        if (!empty($configuratorSet)) {
            $params['configuratorSet'] = $configuratorSet;
            $params['variants'] = $variations;
        }

        $createProduct = false;

        /**
         * @var Article $resource
         */
        $resource = Manager::getResource('Article');

        if (null === $identity) {
            try {
                $existingProduct = $resource->getIdFromNumber($product->getNumber());

                $identity = $identityService->create(
                    $product->getIdentifier(),
                    Product::TYPE,
                    (string) $existingProduct,
                    ShopwareAdapter::NAME
                );
            } catch (\Exception $exception) {
                $createProduct = true;
            }
        }

        if (null !== $identity) {
            if (!$resource->getIdByData(['id' => $identity->getAdapterIdentifier()])) {
                $createProduct = true;
            }
        }

        try {
            if ($createProduct) {
                $productModel = $resource->create($params);

                $identityService->create(
                    $product->getIdentifier(),
                    Product::TYPE,
                    (string)$productModel->getId(),
                    ShopwareAdapter::NAME
                );
            } else {
                $productModel = $resource->update($identity->getAdapterIdentifier(), $params);
            }

            // TODO: fix attributes (create vs update)
            // TODO: create translation and attribute helper

            /**
             * @var DataPersister $attributePersister
             */
            $attributePersister = Shopware()->Container()->get('shopware_attribute.data_persister');
            //$attributePersister->persist($attributes, 's_articles_attributes', $productModel->getId());

            $resource->writeTranslations($productModel->getId(), $translations);
        } catch (\Exception $exception) {
            $logger = Shopware()->Container()->get('plenty_connector.logger');
            $logger->error($exception->getMessage());
            $logger->error($exception->getTraceAsString());
        }

        return true;
    }

    /**
     * @param Product $product
     * @param string $type
     *
     * @return array
     */
    private function getLinkedProducts(Product $product, $type = LinkedProduct::TYPE_SIMILAR)
    {
        /**
         * @var IdentityServiceInterface $identityService
         */
        $identityService = Shopware()->Container()->get('plenty_connector.identity_service');

        $result = [];

        /**
         * @var Article $resource
         */
        $resource = Shopware()->Container()->get('shopware_adapter.shopware_resource.product');

        foreach ($product->getLinkedProducts() as $linkedProduct) {
            if ($linkedProduct->getType() === $type) {
                $productIdentity = $identityService->findOneBy([
                    'objectIdentifier' => $linkedProduct->getProductIdentifier(),
                    'objectType' => Product::TYPE,
                    'adapterName' => PlentymarketsAdapter::NAME,
                ]);

                if (null === $productIdentity) {
                    // TODO: product was not imported, throw event to import it right away

                    continue;
                }

                try {
                    $existingProduct = $resource->getIdByData(['id' => $productIdentity->getAdapterIdentifier()]);

                    $result[$productIdentity->getAdapterIdentifier()] = ['id' => $productIdentity->getAdapterIdentifier(), 'cross' => true];
                } catch (\Exception $exception) {
                    // fail silently
                }

            }
        }

        return $result;
    }

    /**
     * @param Product $product
     *
     * @return array
     */
    private function getConfiguratorSet(Product $product)
    {
        if (empty($product->getVariations())) {
            return [];
        }

        $groups = [];
        foreach ($product->getVariations() as $variation) {
            if (empty($variation->getPrices())) {
                continue;
            }

            $properties = $variation->getProperties();

            foreach ($properties as $property) {
                $propertyName = $property->getName();

                $groups[$propertyName]['name'] = $propertyName;

                foreach ($property->getValues() as $value) {
                    $propertyValue = $value->getValue();

                    $groups[$propertyName]['options'][$propertyValue]['name'] = $propertyValue;
                }
            }
        }

        if (empty($groups)) {
            return [];
        }

        return [
            'name' => $product->getName(),
            'type' => 2,
            'groups' => $groups
        ];
    }

    /**
     * @param Variation $variation
     * @param Product $product
     *
     * @return array
     */
    private function getVariationData(Variation $variation, Product $product)
    {
        /**
         * @var IdentityServiceInterface $identityService
         */
        $identityService = Shopware()->Container()->get('plenty_connector.identity_service');

        /**
         * @var ModelManager $entityManager
         */
        $entityManager = Shopware()->Container()->get('models');
        $customerGroupRepository = $entityManager->getRepository(Group::class);

        $unitIdentity = $identityService->findOneBy([
            'objectIdentifier' => $variation->getUnitIdentifier(),
            'objectType' => Unit::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
        ]);

        if (null === $unitIdentity) {
            throw new \Exception('Missing unit mapping - ' . $variation->getUnitIdentifier());
        }

        $unit = $unitIdentity->getAdapterIdentifier();

        $prices = [];
        foreach ($variation->getPrices() as $price) {

            if (null === $price->getCustomerGroupIdentifier()) {
                $customerGroupKey = 'EK';
            } else {
                $customerGroupIdentity = $identityService->findOneBy([
                    'objectIdentifier' => $price->getCustomerGroupIdentifier(),
                    'objectType' => CustomerGroup::TYPE,
                    'adapterName' => ShopwareAdapter::NAME,
                ]);

                if (null === $customerGroupIdentity) {
                    // TODO: throw
                }

                $group = $customerGroupRepository->find($customerGroupIdentity->getAdapterIdentifier());

                $customerGroupKey = $group->getKey();
            }

            $prices[] = [
                'customerGroupKey' => $customerGroupKey,
                'price' => $price->getPrice(),
                'pseudoPrice' => $price->getPseudoPrice(),
                'from' => $price->getFromAmount(),
                'to' => $price->getToAmount(),
             ];
        }

        $configuratorOptions = [];
        foreach ($variation->getProperties() as $property) {
            foreach ($property->getValues() as $value) {
                $configuratorOptions[] = [
                    'group' => $property->getName(),
                    'option' => $value->getValue(),
                ];
            }
        }

        $images = [];
        foreach ($variation->getImageIdentifiers() as $imageIdentifier) {
            $imageIdentity = $identityService->findOneBy([
                'objectIdentifier' => $imageIdentifier,
                'objectType' => Media::TYPE,
                'adapterName' => ShopwareAdapter::NAME,
            ]);

            if (null === $imageIdentity) {
                continue;
            }

            $images[] = ['mediaId' => $imageIdentity->getAdapterIdentifier()];
        }

        $shopwareVariation = [
            'name' => $product->getName(),
            'number' => $variation->getNumber(),
            'unitId' => $unit,
            'active' => $variation->getActive(),
            'inStock' => $variation->getStock(),
            'isMain' => $variation->isIsMain(),
            'standard' => $variation->isIsMain(),
            'shippingtime' => $variation->getShippingTime(),
            'prices' => $prices,
            'purchasePrice' => $variation->getPurchasePrice(),
            'weight' => $variation->getWeight(),
            'len' => $variation->getLength(),
            'height' => $variation->getHeight(),
            'ean' => $variation->getEan(),
            'images' => $images,
            'minPurchase' => $variation->getMinimumOrderQuantity(),
            'purchaseSteps' => $variation->getIntervalOrderQuantity(),
            'maxPurchase' => $variation->getMaximumOrderQuantity(),
            'shippingFree' => false
        ];

        if (!empty($configuratorOptions)) {
            $shopwareVariation['configuratorOptions'] = $configuratorOptions;
        }

        return $shopwareVariation;
    }
}
