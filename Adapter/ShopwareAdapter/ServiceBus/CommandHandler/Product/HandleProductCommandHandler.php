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
use PlentyConnector\Connector\TransferObject\Product\Barcode\Barcode;
use PlentyConnector\Connector\TransferObject\Product\LinkedProduct\LinkedProduct;
use PlentyConnector\Connector\TransferObject\Product\Product;
use PlentyConnector\Connector\TransferObject\Product\Variation\Variation;
use PlentyConnector\Connector\TransferObject\ShippingProfile\ShippingProfile;
use PlentyConnector\Connector\TransferObject\Shop\Shop;
use PlentyConnector\Connector\TransferObject\Unit\Unit;
use PlentyConnector\Connector\TransferObject\VatRate\VatRate;
use PlentyConnector\Connector\Translation\TranslationHelperInterface;
use PlentyConnector\Connector\ValueObject\Attribute\Attribute;
use Psr\Log\LoggerInterface;
use Shopware\Bundle\AttributeBundle\Service\CrudService;
use Shopware\Bundle\AttributeBundle\Service\DataPersister;
use Shopware\Bundle\AttributeBundle\Service\TypeMapping;
use Shopware\Components\Api\Exception\NotFoundException;
use Shopware\Components\Api\Manager;
use Shopware\Components\Api\Resource\Article;
use Shopware\Components\Api\Resource\Variant;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Article\Detail;
use Shopware\Models\Customer\Group;
use Shopware\Models\Property\Repository;
use Shopware\Models\Shop\Shop as ShopModel;
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
         * @var HandleCommandInterface
         * @var Product                $product
         */
        $product = $command->getTransferObject();

        /**
         * @var ModelManager
         */
        $entityManager = Shopware()->Container()->get('models');

        /**
         * @var IdentityServiceInterface
         */
        $identityService = Shopware()->Container()->get('plenty_connector.identity_service');

        $identity = $identityService->findOneBy([
            'objectIdentifier' => $product->getIdentifier(),
            'objectType'       => Product::TYPE,
            'adapterName'      => ShopwareAdapter::NAME,
        ]);

        $shopIdentifiers = array_filter($product->getShopIdentifiers(), function ($identifier) use ($identityService) {
            $shopIdentity = $identityService->findOneBy([
                'objectIdentifier' => $identifier,
                'objectType'       => Shop::TYPE,
                'adapterName'      => ShopwareAdapter::NAME,
            ]);

            return null !== $shopIdentity;
        });

        if (empty($shopIdentifiers)) {
            return false;
        }

        $vatIdentity = $identityService->findOneBy([
            'adapterName'      => ShopwareAdapter::NAME,
            'objectType'       => VatRate::TYPE,
            'objectIdentifier' => $product->getVatRateIdentifier(),
        ]);

        if (null === $vatIdentity) {
            /**
             * @var LoggerInterface
             */
            $logger = Shopware()->Container()->get('plenty_connector.logger');
            $logger->notice('vat rate not mapped', ['command' => $command]);

            return false;
        }

        $manufacturerIdentity = $identityService->findOneBy([
            'adapterName'      => ShopwareAdapter::NAME,
            'objectType'       => Manufacturer::TYPE,
            'objectIdentifier' => $product->getManufacturerIdentifier(),
        ]);

        if (null === $manufacturerIdentity) {
            /**
             * @var LoggerInterface
             */
            $logger = Shopware()->Container()->get('plenty_connector.logger');
            $logger->notice('manufacturer is missing', ['command' => $command]);
        }

        $images = [];

        $position = 0;
        foreach ($product->getImageIdentifiers() as $imageIdentifier) {
            $imageIdentity = $identityService->findOneBy([
                'objectIdentifier' => $imageIdentifier,
                'objectType'       => Media::TYPE,
                'adapterName'      => ShopwareAdapter::NAME,
            ]);

            if (null === $imageIdentity) {
                /**
                 * @var LoggerInterface
                 */
                $logger = Shopware()->Container()->get('plenty_connector.logger');
                $logger->notice('image is missing', ['command' => $command]);

                return false;
            }

            $images[] = [
                'mediaId'  => $imageIdentity->getAdapterIdentifier(),
                'position' => $position++,
            ];
        }

        $categories = [];
        foreach ($product->getCategoryIdentifiers() as $categoryIdentifier) {
            $categoryIdentity = $identityService->findOneBy([
                'objectIdentifier' => $categoryIdentifier,
                'objectType'       => Category::TYPE,
                'adapterName'      => ShopwareAdapter::NAME,
            ]);

            if (null === $categoryIdentity) {
                continue;
            }

            $categories[] = [
                'id' => $categoryIdentity->getAdapterIdentifier(),
            ];
        }

        $categoryRepository = $entityManager->getRepository(\Shopware\Models\Category\Category::class);
        $shopRepository = $entityManager->getRepository(ShopModel::class);

        $seoCategories = [];
        foreach ($product->getDefaultCategoryIdentifiers() as $categoryIdentifier) {
            $categoryIdentity = $identityService->findOneBy([
                'objectIdentifier' => $categoryIdentifier,
                'objectType'       => Category::TYPE,
                'adapterName'      => ShopwareAdapter::NAME,
            ]);

            if (null === $categoryIdentity) {
                continue;
            }

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
                    'shopId'     => $shop->getId(),
                ];
            }
        }

        /**
         * @var CrudService
         */
        $attributeService = Shopware()->Container()->get('shopware_attribute.crud_service');

        $attributes = [];
        foreach ($product->getAttributes() as $attribute) {
            $attributeConfig = $attributeService->get('s_articles_attributes',
                'plentyconnector_'.$attribute->getKey());

            if (!$attributeConfig) {
                $attributeService->update(
                    's_articles_attributes',
                    'plentyconnector_'.$attribute->getKey(),
                    TypeMapping::TYPE_STRING,
                    [
                        'label'            => 'PlentyConnector '.$attribute->getKey(),
                        'displayInBackend' => true,
                        'translatable'     => true,
                        'custom'           => true,
                    ]
                );

                // throw exception to restart import run
            }

            $entityManager->generateAttributeModels(['s_articles_attributes']);

            $attributes['plentyconnector_'.$attribute->getKey()] = $attribute->getValue();
        }

        foreach ($product->getShippingProfileIdentifiers() as $identifier) {
            $profileIdentity = $identityService->findOneBy([
                'adapterName'      => ShopwareAdapter::NAME,
                'objectType'       => ShippingProfile::TYPE,
                'objectIdentifier' => $identifier,
            ]);

            if (null === $profileIdentity) {
                /**
                 * @var LoggerInterface
                 */
                $logger = Shopware()->Container()->get('plenty_connector.logger');
                $logger->notice('shipping profile not mapped', ['command' => $command]);

                continue;
            }

            $columnName = 'plentyconnector_shippingprofile'.$profileIdentity->getAdapterIdentifier();
            $attributeConfig = $attributeService->get('s_articles_attributes', $columnName);

            if (!$attributeConfig) {
                $attributeService->update(
                    's_articles_attributes',
                    $columnName,
                    TypeMapping::TYPE_BOOLEAN,
                    [
                        'label'            => 'PlentyConnector ShippingProfile '.$profileIdentity->getAdapterIdentifier(),
                        'displayInBackend' => true,
                        'custom'           => true,
                        'defaultValue'     => 0,
                    ]
                );

                $entityManager->generateAttributeModels(['s_articles_attributes']);
            }

            $attributes['plentyconnector_shippingProfile'.$profileIdentity->getAdapterIdentifier()] = 1;
        }

        /**
         * @var TranslationHelperInterface
         */
        $translationHelper = Shopware()->Container()->get('plenty_connector.translation_helper');

        $translations = [];
        foreach ($translationHelper->getLanguageIdentifiers($product) as $languageIdentifier) {
            /**
             * @var Product
             */
            $translatedProduct = $translationHelper->translate($languageIdentifier, $product);

            $languageIdentity = $identityService->findOneBy([
                'adapterName'      => ShopwareAdapter::NAME,
                'objectType'       => Language::TYPE,
                'objectIdentifier' => $languageIdentifier,
            ]);

            if (null === $languageIdentity) {
                /**
                 * @var LoggerInterface
                 */
                $logger = Shopware()->Container()->get('plenty_connector.logger');
                $logger->notice('langauge not mapped', ['command' => $command]);

                continue;
            }

            $shops = $shopRepository->findBy([
                'locale' => $languageIdentity->getAdapterIdentifier(),
            ]);

            $translation = [
                'name'            => $translatedProduct->getName(),
                'description'     => $translatedProduct->getDescription(),
                'descriptionLong' => $translatedProduct->getLongDescription(),
                'keywords'        => $translatedProduct->getMetaKeywords(),
            ];

            foreach ($product->getAttributes() as $attribute) {
                /**
                 * @var Attribute
                 */
                $translatedAttribute = $translationHelper->translate($languageIdentifier, $attribute);

                $key = '__attribute_plentyconnector'.ucfirst($translatedAttribute->getKey());
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
            'filterGroupId'  => $propertyData['filterGroupId'],
            'propertyValues' => $propertyData['propertyValues'],
            //'pseudoSales => 0,
            //'highlight' => false,
            //'priceGroupActive => 0,
            //'crossBundleLook' => true,
            //'mode' => ?
            //'template'
            'availableFrom'        => $product->getAvailableFrom(),
            'availableTo'          => $product->getAvailableTo(),
            'name'                 => $product->getName(),
            'description'          => $product->getDescription(),
            'descriptionLong'      => $product->getLongDescription(),
            'categories'           => $categories,
            'seoCategories'        => $seoCategories,
            'taxId'                => $vatIdentity->getAdapterIdentifier(),
            'lastStock'            => $product->getLimitedStock(),
            'notification'         => true,
            'active'               => $product->getActive(),
            'images'               => $images,
            'similar'              => $this->getLinkedProducts($product, LinkedProduct::TYPE_SIMILAR),
            'related'              => $this->getLinkedProducts($product, LinkedProduct::TYPE_ACCESSORY),
            'metaTitle'            => $product->getMetaTitle(),
            'keywords'             => $product->getMetaKeywords(),
            '__options_categories' => ['replace' => true],
            '__options_similar'    => ['replace' => true],
            '__options_related'    => ['replace' => true],
            '__options_prices'     => ['replace' => true],
            '__options_images'     => ['replace' => true],
        ];

        // TODO: add default supplier via config
        if (null !== $manufacturerIdentity) {
            $params['supplierId'] = $manufacturerIdentity->getAdapterIdentifier();
        }

        $configuratorSet = $this->getConfiguratorSet($product);
        if (!empty($configuratorSet)) {
            $params['configuratorSet'] = $configuratorSet;
        }

        $createProduct = false;

        /**
         * @var Article
         */
        $resource = Manager::getResource('Article');

        if (null === $identity) {
            try {
                $mainVariation = $this->getMainVariation($product);
                $existingProduct = $resource->getIdFromNumber($mainVariation->getNumber());

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
                $identityService->remove($identity);

                $createProduct = true;
            }
        }

        try {
            if ($createProduct) {
                $variations = [];

                foreach ($product->getVariations() as $variation) {
                    if ($variation->isMain()) {
                        continue;
                    }

                    $variations[] = $this->getVariationData($variation, $product);
                }

                $params['mainDetail'] = $this->getVariationData($this->getMainVariation($product), $product);

                if (!empty($variations)) {
                    $params['variants'] = $variations;
                }

                $productModel = $resource->create($params);

                $identityService->create(
                    $product->getIdentifier(),
                    Product::TYPE,
                    (string) $productModel->getId(),
                    ShopwareAdapter::NAME
                );

                $resource->writeTranslations($productModel->getId(), $translations);
            } else {
                $productModel = $resource->update($identity->getAdapterIdentifier(), $params);

                foreach ($product->getVariations() as $variation) {
                    if (empty($variation->getPrices())) {
                        continue;
                    }

                    /**
                     * @var Variant
                     */
                    $variantResource = Manager::getResource('Variant');

                    try {
                        $variant = $variantResource->getOneByNumber($variation->getNumber());
                    } catch (NotFoundException $exception) {
                        $variant = null;
                    }

                    $variationParams = $this->getVariationData($variation, $product);

                    if (null === $variant) {
                        $variationParams['articleId'] = $identity->getAdapterIdentifier();

                        $variantResource->create($variationParams);
                    } else {
                        $variantResource->update($variant['id'], $variationParams);
                    }
                }

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

                $resource->writeTranslations($productModel->getId(), $translations);
            }

            // TODO: fix attributes (create vs update)
            // TODO: create translation and attribute helper

            /**
             * @var DataPersister
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
    }

    /**
     * @param Product $product
     * @param int     $type
     *
     * @return array
     */
    private function getLinkedProducts(Product $product, $type = LinkedProduct::TYPE_SIMILAR)
    {
        /**
         * @var IdentityServiceInterface
         */
        $identityService = Shopware()->Container()->get('plenty_connector.identity_service');

        $result = [];

        foreach ($product->getLinkedProducts() as $linkedProduct) {
            if ($linkedProduct->getType() === $type) {
                $productIdentity = $identityService->findOneBy([
                    'objectIdentifier' => $linkedProduct->getProductIdentifier(),
                    'objectType'       => Product::TYPE,
                    'adapterName'      => ShopwareAdapter::NAME,
                ]);

                if (null === $productIdentity) {
                    // TODO: product was not imported, throw event to import it right away

                    continue;
                }

                try {
                    $result[$productIdentity->getAdapterIdentifier()] = [
                        'id'    => $productIdentity->getAdapterIdentifier(),
                        'cross' => false,
                    ];
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
            'name'   => $product->getName(),
            'type'   => 2,
            'groups' => $groups,
        ];
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
         * @var Repository
         */
        $groupRepository = Shopware()->Models()->getRepository(\Shopware\Models\Property\Group::class);
        /**
         * @var \Shopware\Models\Property\Group
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
                        'name'       => $property->getName(),
                        'filterable' => true,
                    ],
                    'value' => $value->getValue(),
                ];
            }
        }

        return $result;
    }

    /**
     * @param Variation $variation
     * @param Product   $product
     *
     * @return array
     */
    private function getVariationData(Variation $variation, Product $product)
    {
        /**
         * @var IdentityServiceInterface
         */
        $identityService = Shopware()->Container()->get('plenty_connector.identity_service');

        /**
         * @var ModelManager
         */
        $entityManager = Shopware()->Container()->get('models');
        $customerGroupRepository = $entityManager->getRepository(Group::class);

        $unitIdentity = $identityService->findOneBy([
            'objectIdentifier' => $variation->getUnitIdentifier(),
            'objectType'       => Unit::TYPE,
            'adapterName'      => ShopwareAdapter::NAME,
        ]);

        if (null === $unitIdentity) {
            throw new \Exception('Missing unit mapping - '.$variation->getUnitIdentifier());
        }

        $prices = [];
        foreach ($variation->getPrices() as $price) {
            if (null === $price->getCustomerGroupIdentifier()) {
                $customerGroupKey = 'EK';
            } else {
                $customerGroupIdentity = $identityService->findOneBy([
                    'objectIdentifier' => $price->getCustomerGroupIdentifier(),
                    'objectType'       => CustomerGroup::TYPE,
                    'adapterName'      => ShopwareAdapter::NAME,
                ]);

                if (null === $customerGroupIdentity) {
                    // TODO: throw
                }

                $group = $customerGroupRepository->find($customerGroupIdentity->getAdapterIdentifier());

                $customerGroupKey = $group->getKey();
            }

            $prices[] = [
                'customerGroupKey' => $customerGroupKey,
                'price'            => $price->getPrice(),
                'pseudoPrice'      => $price->getPseudoPrice(),
                'from'             => $price->getFromAmount(),
                'to'               => $price->getToAmount(),
            ];
        }

        $configuratorOptions = [];
        foreach ($variation->getProperties() as $property) {
            foreach ($property->getValues() as $value) {
                $configuratorOptions[] = [
                    'group'  => $property->getName(),
                    'option' => $value->getValue(),
                ];
            }
        }

        $images = [];
        $position = 0;

        foreach ($variation->getImageIdentifiers() as $imageIdentifier) {
            $imageIdentity = $identityService->findOneBy([
                'objectIdentifier' => $imageIdentifier,
                'objectType'       => Media::TYPE,
                'adapterName'      => ShopwareAdapter::NAME,
            ]);

            if (null === $imageIdentity) {
                continue;
            }

            $images[] = [
                'mediaId'  => $imageIdentity->getAdapterIdentifier(),
                'position' => $position++,
            ];
        }

        /**
         * @var Barcode[]
         */
        $barcodes = array_filter($variation->getBarcodes(), function (Barcode $barcode) {
            return $barcode->getType() === Barcode::TYPE_GTIN13;
        });

        if (!empty($barcodes)) {
            $barcode = array_shift($barcodes);
            $ean = $barcode->getCode();
        } else {
            $ean = '';
        }

        $shopwareVariation = [
            'name'          => $product->getName(),
            'number'        => $variation->getNumber(),
            'position'      => $variation->getPosition(),
            'unitId'        => $unitIdentity->getAdapterIdentifier(),
            'active'        => $variation->getActive(),
            'inStock'       => $variation->getStock(),
            'isMain'        => $variation->isMain(),
            'standard'      => $variation->isMain(),
            'shippingtime'  => $variation->getShippingTime(),
            'prices'        => $prices,
            'purchasePrice' => $variation->getPurchasePrice(),
            'weight'        => $variation->getWeight(),
            'len'           => $variation->getLength(),
            'height'        => $variation->getHeight(),
            'ean'           => $ean,
            'images'        => $images,
            'minPurchase'   => $variation->getMinimumOrderQuantity(),
            'purchaseSteps' => $variation->getIntervalOrderQuantity(),
            'maxPurchase'   => $variation->getMaximumOrderQuantity(),
            'shippingFree'  => false,
        ];

        if (!empty($configuratorOptions)) {
            $shopwareVariation['configuratorOptions'] = $configuratorOptions;
        }

        return $shopwareVariation;
    }
}
