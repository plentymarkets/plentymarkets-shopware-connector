<?php

namespace ShopwareAdapter\ServiceBus\CommandHandler\Product;

use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\ServiceBus\Command\CommandInterface;
use PlentyConnector\Connector\ServiceBus\Command\HandleCommandInterface;
use PlentyConnector\Connector\ServiceBus\Command\Product\HandleProductCommand;
use PlentyConnector\Connector\ServiceBus\CommandHandler\CommandHandlerInterface;
use PlentyConnector\Connector\TransferObject\Category\Category;
use PlentyConnector\Connector\TransferObject\Language\Language;
use PlentyConnector\Connector\TransferObject\Manufacturer\Manufacturer;
use PlentyConnector\Connector\TransferObject\Media\Media;
use PlentyConnector\Connector\TransferObject\Product\LinkedProduct\LinkedProduct;
use PlentyConnector\Connector\TransferObject\Product\Product;
use PlentyConnector\Connector\TransferObject\Product\Variation\Variation;
use PlentyConnector\Connector\TransferObject\ShippingProfile\ShippingProfile;
use PlentyConnector\Connector\TransferObject\Shop\Shop;
use PlentyConnector\Connector\TransferObject\VatRate\VatRate;
use PlentyConnector\Connector\Translation\TranslationHelperInterface;
use PlentyConnector\Connector\ValueObject\Attribute\Attribute;
use Psr\Log\LoggerInterface;
use Shopware\Components\Api\Exception\NotFoundException;
use Shopware\Components\Api\Manager;
use Shopware\Components\Api\Resource\Article;
use Shopware\Components\Api\Resource\Resource;
use Shopware\Components\Api\Resource\Variant;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Article\Detail;
use Shopware\Models\Property\Repository;
use Shopware\Models\Shop\Shop as ShopModel;
use ShopwareAdapter\Helper\AttributeHelper;
use ShopwareAdapter\RequestGenerator\Product\ConfiguratorSet\ConfiguratorSetRequestGeneratorInterface;
use ShopwareAdapter\RequestGenerator\Product\Variation\VariationRequestGeneratorInterface;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class HandleProductCommandHandler.
 */
class HandleProductCommandHandler implements CommandHandlerInterface
{
    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * @var AttributeHelper
     */
    private $attributeHelper;

    /**
     * @var ConfiguratorSetRequestGeneratorInterface
     */
    private $configuratorSetRequestGenerator;

    /**
     * @var VariationRequestGeneratorInterface
     */
    private $variationRequestGenerator;

    /**
     * HandleProductCommandHandler constructor.
     *
     * @param IdentityServiceInterface                 $identityService
     * @param AttributeHelper                          $attributeHelper
     * @param ConfiguratorSetRequestGeneratorInterface $configuratorSetRequestGenerator
     * @param VariationRequestGeneratorInterface       $variationRequestGenerator
     */
    public function __construct(
        IdentityServiceInterface $identityService,
        AttributeHelper $attributeHelper,
        ConfiguratorSetRequestGeneratorInterface $configuratorSetRequestGenerator,
        VariationRequestGeneratorInterface $variationRequestGenerator
    ) {
        $this->identityService = $identityService;
        $this->attributeHelper = $attributeHelper;
        $this->configuratorSetRequestGenerator = $configuratorSetRequestGenerator;
        $this->variationRequestGenerator = $variationRequestGenerator;
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
         * @var Product                $product
         */
        $product = $command->getTransferObject();

        /**
         * @var ModelManager $entityManager
         */
        $entityManager = Shopware()->Container()->get('models');

        $identity = $this->identityService->findOneBy([
            'objectIdentifier' => $product->getIdentifier(),
            'objectType' => Product::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
        ]);

        $shopIdentifiers = array_filter($product->getShopIdentifiers(), function ($identifier) {
            $shopIdentity = $this->identityService->findOneBy([
                'objectIdentifier' => $identifier,
                'objectType' => Shop::TYPE,
                'adapterName' => ShopwareAdapter::NAME,
            ]);

            return null !== $shopIdentity;
        });

        if (empty($shopIdentifiers)) {
            return false;
        }

        $vatIdentity = $this->identityService->findOneBy([
            'adapterName' => ShopwareAdapter::NAME,
            'objectType' => VatRate::TYPE,
            'objectIdentifier' => $product->getVatRateIdentifier(),
        ]);

        if (null === $vatIdentity) {
            /**
             * @var LoggerInterface $logger
             */
            $logger = Shopware()->Container()->get('plenty_connector.logger');
            $logger->notice('vat rate not mapped - ' . $product->getVatRateIdentifier(), ['command' => $command]);

            return false;
        }

        $manufacturerIdentity = $this->identityService->findOneBy([
            'adapterName' => ShopwareAdapter::NAME,
            'objectType' => Manufacturer::TYPE,
            'objectIdentifier' => $product->getManufacturerIdentifier(),
        ]);

        if (null === $manufacturerIdentity) {
            /**
             * @var LoggerInterface $logger
             */
            $logger = Shopware()->Container()->get('plenty_connector.logger');
            $logger->notice('manufacturer is missing - ' . $product->getManufacturerIdentifier(),
                ['command' => $command]);

            return false;
        }

        $images = [];
        foreach ($product->getImages() as $image) {
            $shopIdentifiers = array_filter($image->getShopIdentifiers(), function ($shop) {
                $identity = $this->identityService->findOneBy([
                    'objectIdentifier' => (string) $shop,
                    'objectType' => Shop::TYPE,
                    'adapterName' => ShopwareAdapter::NAME,
                ]);

                return $identity !== null;
            });

            if (empty($shopIdentifiers)) {
                continue;
            }

            $imageIdentity = $this->identityService->findOneBy([
                'objectIdentifier' => $image->getMediaIdentifier(),
                'objectType' => Media::TYPE,
                'adapterName' => ShopwareAdapter::NAME,
            ]);

            if (null === $imageIdentity) {
                /**
                 * @var LoggerInterface $logger
                 */
                $logger = Shopware()->Container()->get('plenty_connector.logger');
                $logger->notice('image is missing - ' . $image->getMediaIdentifier(), ['command' => $command]);

                return false;
            }

            $images[] = [
                'mediaId' => $imageIdentity->getAdapterIdentifier(),
                'position' => $image->getPosition(),
            ];
        }

        $categories = [];
        foreach ($product->getCategoryIdentifiers() as $categoryIdentifier) {
            $categoryIdentities = $this->identityService->findBy([
                'objectIdentifier' => $categoryIdentifier,
                'objectType' => Category::TYPE,
                'adapterName' => ShopwareAdapter::NAME,
            ]);

            foreach ($categoryIdentities as $categoryIdentity) {
                $categories[] = [
                    'id' => $categoryIdentity->getAdapterIdentifier(),
                ];
            }
        }

        $categoryRepository = $entityManager->getRepository(\Shopware\Models\Category\Category::class);
        $shopRepository = $entityManager->getRepository(ShopModel::class);

        $seoCategories = [];
        foreach ($product->getDefaultCategoryIdentifiers() as $categoryIdentifier) {
            $categoryIdentities = $this->identityService->findBy([
                'objectIdentifier' => $categoryIdentifier,
                'objectType' => Category::TYPE,
                'adapterName' => ShopwareAdapter::NAME,
            ]);

            foreach ($categoryIdentities as $categoryIdentity) {
                $category = $categoryRepository->find($categoryIdentity->getAdapterIdentifier());

                if (null === $category) {
                    continue;
                }

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
        }

        foreach ($product->getShippingProfileIdentifiers() as $identifier) {
            $profileIdentity = $this->identityService->findOneBy([
                'adapterName' => ShopwareAdapter::NAME,
                'objectType' => ShippingProfile::TYPE,
                'objectIdentifier' => $identifier,
            ]);

            if (null === $profileIdentity) {
                /**
                 * @var LoggerInterface $logger
                 */
                $logger = Shopware()->Container()->get('plenty_connector.logger');
                $logger->notice('shipping profile not mapped - ' . $identifier, ['command' => $command]);

                continue;
            }

            $attributes = $product->getAttributes();

            $existingAttributes = array_filter($attributes, function (Attribute $attribute) use ($profileIdentity) {
                return $attribute->getKey() === 'shippingProfile' . $profileIdentity->getAdapterIdentifier();
            });

            if (!empty($existingAttributes)) {
                /**
                 * @var LoggerInterface $logger
                 */
                $logger = Shopware()->Container()->get('plenty_connector.logger');
                $logger->notice('shippingProfile is not a allowed attribute key', ['command' => $command]);

                continue;
            }

            $attributes[] = Attribute::fromArray([
                'key' => 'shippingProfile' . $profileIdentity->getAdapterIdentifier(),
                'value' => $profileIdentity->getObjectIdentifier(),
            ]);

            $product->setAttributes($attributes);
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

            $languageIdentity = $this->identityService->findOneBy([
                'adapterName' => ShopwareAdapter::NAME,
                'objectType' => Language::TYPE,
                'objectIdentifier' => $languageIdentifier,
            ]);

            if (null === $languageIdentity) {
                /**
                 * @var LoggerInterface $logger
                 */
                $logger = Shopware()->Container()->get('plenty_connector.logger');
                $logger->notice('langauge not mapped - ' . $languageIdentifier, ['command' => $command]);

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

                $key = 'plentyConnector' . ucfirst($attribute->getKey());
                $translation[$key] = $translatedAttribute->getValue();
            }

            foreach ($shops as $shop) {
                $translation['shopId'] = $shop->getId();

                $translations[] = $translation;
            }
        }

        $propertyData = $this->getPropertyData($product);

        $params = [
            //'priceGroupId' => 0,
            'filterGroupId' => $propertyData['filterGroupId'],
            'propertyValues' => $propertyData['propertyValues'],
            //'pseudoSales => 0,
            //'highlight' => false,
            //'priceGroupActive => 0,
            //'crossBundleLook' => true,
            //'mode' => ?
            //'template'
            'availableFrom' => $product->getAvailableFrom(),
            'availableTo' => $product->getAvailableTo(),
            'name' => $product->getName(),
            'description' => $product->getMetaDescription(),
            'descriptionLong' => !empty($product->getLongDescription()) ? $product->getLongDescription() : $product->getDescription(),
            'categories' => $categories,
            'seoCategories' => $seoCategories,
            'taxId' => $vatIdentity->getAdapterIdentifier(),
            'lastStock' => $product->hasStockLimitation(),
            'notification' => true,
            'active' => $product->isActive(),
            'images' => $images,
            'similar' => $this->getLinkedProducts($product, LinkedProduct::TYPE_SIMILAR),
            'related' => $this->getLinkedProducts($product, LinkedProduct::TYPE_ACCESSORY),
            'metaTitle' => $product->getMetaTitle(),
            'keywords' => $product->getMetaKeywords(),
            'supplierId' => $manufacturerIdentity->getAdapterIdentifier(),
            '__options_categories' => ['replace' => true],
            '__options_seoCategories' => ['replace' => true],
            '__options_similar' => ['replace' => true],
            '__options_related' => ['replace' => true],
            '__options_prices' => ['replace' => true],
            '__options_images' => ['replace' => true],
        ];

        $configuratorSet = $this->configuratorSetRequestGenerator->generate($product);
        if (!empty($configuratorSet)) {
            $params['configuratorSet'] = $configuratorSet;
        }

        $createProduct = false;

        /**
         * @var Article $resource
         */
        $resource = Manager::getResource('Article');

        if (null === $identity) {
            try {
                $mainVariation = $this->getMainVariation($product);
                $existingProduct = $resource->getIdFromNumber($mainVariation->getNumber());

                $identity = $this->identityService->create(
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
                $this->identityService->remove($identity);

                $createProduct = true;
            }
        }

        if ($createProduct) {
            $variations = [];

            foreach ($product->getVariations() as $variation) {
                if ($variation->isMain()) {
                    continue;
                }

                $variations[] = $this->variationRequestGenerator->generate($variation, $product);
            }

            $params['mainDetail'] = $this->variationRequestGenerator->generate($this->getMainVariation($product), $product);

            if (!empty($variations)) {
                $params['variants'] = $variations;
            }

            $productModel = $resource->create($params);

            $this->identityService->create(
                $product->getIdentifier(),
                Product::TYPE,
                (string) $productModel->getId(),
                ShopwareAdapter::NAME
            );

            /**
             * @var Variant $variantResource
             */
            $variantResource = Manager::getResource('Variant');

            foreach ($product->getVariations() as $variation) {
                try {
                    $variant = $variantResource->getOneByNumber($variation->getNumber());
                } catch (NotFoundException $exception) {
                    continue;
                }

                $this->attributeHelper->saveAttributes(
                    (int) $variant['id'],
                    $product->getAttributes(),
                    's_articles_attributes'
                );
            }
        } else {
            $productModel = $resource->update($identity->getAdapterIdentifier(), $params);

            foreach ($product->getVariations() as $variation) {
                if (empty($variation->getPrices())) {
                    continue;
                }

                /**
                 * @var Variant $variantResource
                 */
                $variantResource = Manager::getResource('Variant');
                $variantResource->setResultMode(Resource::HYDRATE_ARRAY);

                try {
                    $variant = $variantResource->getOneByNumber($variation->getNumber());
                } catch (NotFoundException $exception) {
                    $variant = null;
                }

                $variationParams = $this->variationRequestGenerator->generate($variation, $product);

                if (null === $variant) {
                    $variationParams['articleId'] = $identity->getAdapterIdentifier();

                    $variant = $variantResource->create($variationParams);

                    $attributes = array_merge($product->getAttributes(), $variation->getAttributes());

                    $this->attributeHelper->saveAttributes(
                        (int) $variant->getId(),
                        $attributes,
                        's_articles_attributes'
                    );
                } else {
                    $variantResource->update($variant['id'], $variationParams);

                    $attributes = array_merge($product->getAttributes(), $variation->getAttributes());

                    $this->attributeHelper->saveAttributes(
                        (int) $variant['id'],
                        $attributes,
                        's_articles_attributes'
                    );
                }
            }

            foreach ($productModel->getDetails() as $detail) {
                $found = false;
                foreach ($product->getVariations() as $variation) {
                    if ($variation->getNumber() === $detail->getNumber()) {
                        $found = true;
                    }
                }

                if ($found) {
                    continue;
                }

                $entityManager->remove($detail);
            }

            $entityManager->flush();

            $mainVariation = $this->getMainVariation($product);

            if (null !== $mainVariation) {
                $entityManager = Shopware()->Container()->get('models');

                $productRepository = $entityManager->getRepository(\Shopware\Models\Article\Article::class);
                $detailRepository = $entityManager->getRepository(Detail::class);

                $mainDetail = $detailRepository->findOneBy(['number' => $mainVariation->getNumber()]);

                $productModel = $productRepository->find($identity->getAdapterIdentifier());
                $productModel->setMainDetail($mainDetail);

                $entityManager->persist($productModel);
                $entityManager->flush();
            }
        }

        $resource->writeTranslations($productModel->getId(), $translations);

        return true;
    }

    /**
     * @param Product $product
     *
     * @return null|Variation
     */
    private function getMainVariation(Product $product)
    {
        foreach ($product->getVariations() as $variation) {
            if ($variation->isMain()) {
                return $variation;
            }
        }

        return null;
    }

    /**
     * @param Product $product
     * @param int     $type
     *
     * @return array
     */
    private function getLinkedProducts(Product $product, $type = LinkedProduct::TYPE_SIMILAR)
    {
        $result = [];

        /**
         * @var Article $resource
         */
        $resource = Manager::getResource('Article');

        foreach ($product->getLinkedProducts() as $linkedProduct) {
            if ($linkedProduct->getType() === $type) {
                $productIdentity = $this->identityService->findOneBy([
                    'objectIdentifier' => $linkedProduct->getProductIdentifier(),
                    'objectType' => Product::TYPE,
                    'adapterName' => ShopwareAdapter::NAME,
                ]);

                if (null === $productIdentity) {
                    continue;
                }

                $productExists = $resource->getIdByData(['id' => $productIdentity->getAdapterIdentifier()]);
                if (!$productExists) {
                    continue;
                }

                $result[$productIdentity->getAdapterIdentifier()] = [
                    'id' => $productIdentity->getAdapterIdentifier(),
                    'number' => null,
                    'position' => $linkedProduct->getPosition(),
                    'cross' => false,
                ];
            }
        }

        return $result;
    }

    /**
     * @param Product $product
     *
     * @return array
     */
    private function getPropertyData(Product $product)
    {
        $result = [];

        /**
         * @var Repository $groupRepository
         */
        $groupRepository = Shopware()->Models()->getRepository(\Shopware\Models\Property\Group::class);
        /**
         * @var \Shopware\Models\Property\Group $propertyGroup
         */
        $propertyGroup = $groupRepository->findOneBy(['name' => 'PlentyConnector']);

        if (null === $propertyGroup) {
            $propertyGroup = new \Shopware\Models\Property\Group();
            $propertyGroup->setName('PlentyConnector');
            $propertyGroup->setPosition(1);
            $propertyGroup->setComparable(true);
            $propertyGroup->setSortMode(true);

            Shopware()->Models()->persist($propertyGroup);
            Shopware()->Models()->flush();
        }

        $result['filterGroupId'] = $propertyGroup->getId();
        $result['propertyValues'] = [];

        foreach ($product->getProperties() as $property) {
            foreach ($property->getValues() as $value) {
                $result['propertyValues'][] = [
                    'option' => [
                        'name' => $property->getName(),
                    ],
                    'value' => $value->getValue(),
                ];
            }
        }

        return $result;
    }
}
