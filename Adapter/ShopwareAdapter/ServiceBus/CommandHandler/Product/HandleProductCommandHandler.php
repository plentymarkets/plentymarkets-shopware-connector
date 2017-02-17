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
use PlentyConnector\Connector\TransferObject\Product\Product;
use PlentyConnector\Connector\TransferObject\Product\ProductInterface;
use PlentyConnector\Connector\TransferObject\Product\Variation\Variation;
use PlentyConnector\Connector\TransferObject\ShippingProfile\ShippingProfile;
use PlentyConnector\Connector\TransferObject\Unit\Unit;
use PlentyConnector\Connector\TransferObject\VatRate\VatRate;
use PlentyConnector\Connector\Translation\TranslationHelperInterface;
use PlentyConnector\Connector\ValueObject\Attribute\AttributeInterface;
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
         * @var ProductInterface $product
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
            'adapterIdentifier' => $product->getNumber(),
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

            return false;
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

            $attributes['plentyconnector' . ucfirst($attribute->getKey())] = $attribute->getValue();
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

            $attributeKey = 'plentyconnector_shippingProfile' . $profileIdentity->getAdapterIdentifier();
            $attributeConfig = $attributeService->get('s_articles_attributes', $attributeKey);

            if (!$attributeConfig) {
                $attributeService->update(
                    's_articles_attributes',
                    $attributeConfig,
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

            $attributes['plentyconnectorShippingProfile' . $profileIdentity->getAdapterIdentifier()] = 1;
        }

        /**
         * @var TranslationHelperInterface $translationHelper
         */
        $translationHelper = Shopware()->Container()->get('plenty_connector.translation_helper');

        $translations = [];
        foreach ($translationHelper->getLanguageIdentifiers($product) as $languageIdentifier) {
            /**
             * @var ProductInterface $translatedProduct
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
                 * @var AttributeInterface $translatedAttribute
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

        $mainDetail = null;

        $variations = [];
        foreach ($product->getVariations() as $variation) {
            $variations[] = $this->getVariationData($variation, $product);
        }

        /* $configuratorSet = [
        'name' => '',
            'type' => '',
            'groups' => [
                [
                    'name' => 'Packungsgröße',
                    'options' => [
                        [
                            "name" => '10 Stück',
                        ],
                        [
                            "name" => '20 Stück',
                        ],
                        [
                            "name" => '30 Stück',
                        ]
                    ]
                ]
            ]
        ]*/

        $params = [
            //'priceGroupId' => 0,
            //'filterGroupId' => 0,
            //'configuratorSetId'
            //'pseudoSales => 0,
            //'highlight' => false,
            //'priceGroupActive => 0,
            //'crossBundleLook' => true,
            //'notification' => true,
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
            'supplierId' => $manufacturerIdentity->getAdapterIdentifier(),
            //'lastStock' => 1,
            'notification' => 1,
            'variants' => $variations,
            'active' => true,
            //'added' => date_create_from_format('d.m.Y', $product['einstelldatum']),
            'images' => $images,
            'metaTitle' => $product->getMetaTitle(),
            'keywords' => $product->getMetaKeywords(),
            //'similar' => $similar,
            '__options_categories' => ['replace' => true],
            '__options_images' => ['replace' => true],
            '__options_similar' => ['replace' => true],
            '__options_prices' => ['replace' => true],
            '__options_variants' => ['replace' => true],
        ];

        $createProduct = false;

        /**
         * @var Article $resource
         */
        $resource = Manager::getResource('Article');

        if (null === $identity) {
            try {
                $existingProduct = $resource->getOneByNumber($product->getNumber());

                $identity = $identityService->create(
                    $product->getIdentifier(),
                    Product::TYPE,
                    (string)$existingProduct['mainDetail']['number'],
                    ShopwareAdapter::NAME
                );
            } catch (\Exception $exception) {
                $createProduct = true;
            }
        }

        if ($createProduct) {
            $productModel = $resource->create($params);

            $identityService->create(
                $product->getIdentifier(),
                Product::TYPE,
                (string)$product->getNumber(),
                ShopwareAdapter::NAME
            );
        } else {
            $productModel = $resource->update($resource->getIdFromNumber($identity->getAdapterIdentifier()), $params);
        }

        /**
         * @var DataPersister $attributePersister
         */
        $attributePersister = Shopware()->Container()->get('shopware_attribute.data_persister');
        $attributePersister->persist($attributes, 's_articles_attributes', $productModel->getId());

        $resource->writeTranslations($productModel->getId(), $translations);

        return true;
    }

    /**
     * @param Variation $variation
     * @param ProductInterface $product
     *
     * @return array
     */
    private function getVariationData(Variation $variation, ProductInterface $product)
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
            $customerGroupIdentity = $identityService->findOneBy([
                'objectIdentifier' => $price->getCustomerGroupIdentifier(),
                'objectType' => CustomerGroup::TYPE,
                'adapterName' => ShopwareAdapter::NAME,
            ]);

            if (null === $customerGroupIdentity) {
                // throw
            }

            $group = $customerGroupRepository->find($customerGroupIdentity->getAdapterIdentifier());

            $prices[] = [
                'customerGroupKey' => $group->getKey(),
                'price' => $price->getPrice(),
                'pseudoPrice' => $price->getPseudoPrice(),
                'from' => 1,
                'to' => null, // or
                'percent' => 0,
            ];
        }

        static $i = 1;

        $configuratorOptions = [
            [
                'group' => 'Packungsgröße',
                'option' => $i . '0 Stück',
            ],
        ];

        ++$i;

        return [
            'name' => $product->getName(),
            'number' => $variation->getNumber(),
            'unitId' => $unit,
            'active' => true,
            'inStock' => $variation->getStock(),
            'isMain' => $variation->isIsMain(),
            'standard' => $variation->isIsMain(),
            //'shippingtime' => $shippingTime,
            //'weight' => $product['gewicht'],
            'prices' => $prices,
            //'additionalText'
            //'stockMin'
            //'weight'
            //'len'
            //'height'
            //'ean'
            //'position'
            //'minPurchase'
            //'purchaseSteps'
            //'maxPurchase'
            //'purchaseUnit'??
            //'shippingFree' => false
            'configuratorOptions' => $configuratorOptions,
            //'attribute'
        ];
    }
}
