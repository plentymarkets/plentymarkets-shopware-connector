<?php

namespace ShopwareAdapter\RequestGenerator\Product;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use InvalidArgumentException;
use PlentyConnector\Connector\ConfigService\ConfigServiceInterface;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\TransferObject\Category\Category;
use PlentyConnector\Connector\TransferObject\Manufacturer\Manufacturer;
use PlentyConnector\Connector\TransferObject\Media\Media;
use PlentyConnector\Connector\TransferObject\Product\Badge\Badge;
use PlentyConnector\Connector\TransferObject\Product\LinkedProduct\LinkedProduct;
use PlentyConnector\Connector\TransferObject\Product\Product;
use PlentyConnector\Connector\TransferObject\ShippingProfile\ShippingProfile;
use PlentyConnector\Connector\TransferObject\Shop\Shop;
use PlentyConnector\Connector\TransferObject\VatRate\VatRate;
use PlentyConnector\Connector\ValueObject\Attribute\Attribute;
use Psr\Log\LoggerInterface;
use Shopware\Models\Article\Article;
use Shopware\Models\Category\Category as CategoryModel;
use Shopware\Models\Category\Repository as CategoryRepository;
use Shopware\Models\Property\Group as GroupModel;
use Shopware\Models\Shop\Repository as ShopRepository;
use Shopware\Models\Shop\Shop as ShopModel;
use ShopwareAdapter\RequestGenerator\Product\ConfiguratorSet\ConfiguratorSetRequestGeneratorInterface;
use ShopwareAdapter\ShopwareAdapter;

class ProductRequestGenerator implements ProductRequestGeneratorInterface
{
    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ConfiguratorSetRequestGeneratorInterface
     */
    private $configuratorSetRequestGenerator;

    /**
     * @var ConfigServiceInterface
     */
    private $config;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    private $categories;

    /**
     * ProductRequestGenerator constructor.
     *
     * @param IdentityServiceInterface                 $identityService
     * @param EntityManagerInterface                   $entityManager
     * @param ConfiguratorSetRequestGeneratorInterface $configuratorSetRequestGenerator
     * @param ConfigServiceInterface                   $config
     * @param LoggerInterface                          $logger
     */
    public function __construct(
        IdentityServiceInterface $identityService,
        EntityManagerInterface $entityManager,
        ConfiguratorSetRequestGeneratorInterface $configuratorSetRequestGenerator,
        ConfigServiceInterface $config,
        LoggerInterface $logger
    ) {
        $this->identityService = $identityService;
        $this->entityManager = $entityManager;
        $this->configuratorSetRequestGenerator = $configuratorSetRequestGenerator;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(Product $product)
    {
        $this->addShippingProfilesAsAttributes($product);

        $shopIdentifiers = array_filter($product->getShopIdentifiers(), function ($identifier) {
            $shopIdentity = $this->identityService->findOneBy([
                'objectIdentifier' => $identifier,
                'objectType' => Shop::TYPE,
                'adapterName' => ShopwareAdapter::NAME,
            ]);

            return null !== $shopIdentity;
        });

        if (empty($shopIdentifiers)) {
            return [];
        }

        $vatIdentity = $this->identityService->findOneBy([
            'objectIdentifier' => $product->getVatRateIdentifier(),
            'objectType' => VatRate::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
        ]);

        if (null === $vatIdentity) {
            throw new InvalidArgumentException('vat rate not mapped - ' . $product->getVatRateIdentifier());
        }

        $manufacturerIdentity = $this->identityService->findOneBy([
            'adapterName' => ShopwareAdapter::NAME,
            'objectType' => Manufacturer::TYPE,
            'objectIdentifier' => $product->getManufacturerIdentifier(),
        ]);

        if (null === $manufacturerIdentity) {
            throw new InvalidArgumentException('manufacturer is missing - ' . $product->getManufacturerIdentifier());
        }

        $propertyData = $this->getPropertyData($product);

        $params = [
            'filterGroupId' => $propertyData['filterGroupId'],
            'propertyValues' => $propertyData['propertyValues'],
            'mainDetail' => [
                'number' => $product->getNumber(),
            ],
            'availableFrom' => $product->getAvailableFrom(),
            'availableTo' => $product->getAvailableTo(),
            'name' => $product->getName(),
            'description' => $product->getMetaDescription(),
            'descriptionLong' => !empty($product->getLongDescription()) ? $product->getLongDescription() : $product->getDescription(),
            'categories' => $this->getCategories($product),
            'seoCategories' => $this->getSeoCategories($product),
            'taxId' => $vatIdentity->getAdapterIdentifier(),
            'lastStock' => $product->hasStockLimitation(),
            'notification' => $this->config->get('item_notification') === 'true' ? 1 : 0,
            'active' => $product->isActive(),
            'highlight' => $this->getHighlightFlag($product),
            'images' => $this->getImages($product),
            'similar' => $this->getLinkedProducts($product),
            'related' => $this->getLinkedProducts($product, LinkedProduct::TYPE_ACCESSORY),
            'metaTitle' => $product->getMetaTitle(),
            'keywords' => $product->getMetaKeywords(),
            'changed' => (new DateTime('now'))->format('Y-m-d H:i:s'),
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

        return $params;
    }

    /**
     * @param Product $product
     */
    private function addShippingProfilesAsAttributes(Product $product)
    {
        $allProfileIdentities = $this->identityService->findBy([
            'objectType' => ShippingProfile::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
        ]);

        $shippingAttributes = [];
        foreach ($allProfileIdentities as $identity) {
            $shippingAttributes['shippingProfile' . $identity->getAdapterIdentifier()] = '';
        }

        $attributes = $product->getAttributes();

        foreach ($product->getShippingProfileIdentifiers() as $identifier) {
            $profileIdentity = $this->identityService->findOneBy([
                'objectIdentifier' => $identifier,
                'objectType' => ShippingProfile::TYPE,
                'adapterName' => ShopwareAdapter::NAME,
            ]);

            if (null === $profileIdentity) {
                continue;
            }

            $existingAttributes = array_filter($attributes, function (Attribute $attribute) use ($profileIdentity) {
                return $attribute->getKey() === 'shippingProfile' . $profileIdentity->getAdapterIdentifier();
            });

            if (!empty($existingAttributes)) {
                $this->logger->notice('shippingProfile is not a allowed attribute key');

                continue;
            }

            $shippingAttributes['shippingProfile' . $profileIdentity->getAdapterIdentifier()] = $profileIdentity->getObjectIdentifier();
        }

        foreach ($shippingAttributes as $key => $value) {
            $attributes[] = Attribute::fromArray([
                'key' => $key,
                'value' => $value,
            ]);
        }

        $product->setAttributes($attributes);
    }

    /**
     * @param Product $product
     * @param string  $type
     *
     * @return array
     */
    private function getLinkedProducts(Product $product, $type = LinkedProduct::TYPE_SIMILAR)
    {
        $result = [];

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

                /**
                 * @var EntityRepository $productRepository
                 */
                $productRepository = $this->entityManager->getRepository(Article::class);

                $productExists = $productRepository->find($productIdentity->getAdapterIdentifier());

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
        /**
         * @var EntityRepository $groupRepository
         */
        $groupRepository = $this->entityManager->getRepository(GroupModel::class);

        /**
         * @var GroupModel $propertyGroup
         */
        $propertyGroup = $groupRepository->findOneBy(['name' => 'PlentyConnector']);

        if (null === $propertyGroup) {
            $propertyGroup = new GroupModel();
            $propertyGroup->setName('PlentyConnector');
            $propertyGroup->setPosition(1);
            $propertyGroup->setComparable(true);
            $propertyGroup->setSortMode(1);

            $this->entityManager->persist($propertyGroup);
            $this->entityManager->flush();
            $this->entityManager->clear();
        }

        $result = [];
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

    /**
     * @param Product $product
     *
     * @return array|bool
     */
    private function getImages(Product $product)
    {
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
                $this->logger->notice('image is missing - ' . $image->getMediaIdentifier());

                return false;
            }

            $images[] = [
                'mediaId' => $imageIdentity->getAdapterIdentifier(),
                'position' => $image->getPosition(),
            ];
        }

        return $images;
    }

    /**
     * @param Product $product
     *
     * @return array
     */
    private function getCategories(Product $product)
    {
        /**
         * @var CategoryRepository $categoryRepository
         */
        $categoryRepository = $this->entityManager->getRepository(CategoryModel::class);

        /**
         * @var ShopRepository $shopRepository
         */
        $shopRepository = $this->entityManager->getRepository(ShopModel::class);

        $shopCategories = [];

        foreach ($product->getShopIdentifiers() as $shopIdentifier) {
            $identity = $this->identityService->findOneBy([
                    'objectIdentifier' => (string) $shopIdentifier,
                    'objectType' => Shop::TYPE,
                    'adapterName' => ShopwareAdapter::NAME,
            ]);

            if ($identity === null) {
                continue;
            }

            /**
             * @var ShopModel $shop
             */
            $shop = $shopRepository->getById($identity->getAdapterIdentifier());

            if ($shop !== null) {
                $shopCategories[] = $shop->getCategory()->getId();
            }
        }

        $this->categories = [];

        foreach ($product->getCategoryIdentifiers() as $categoryIdentifier) {
            $categoryIdentities = $this->identityService->findBy([
                'objectIdentifier' => $categoryIdentifier,
                'objectType' => Category::TYPE,
                'adapterName' => ShopwareAdapter::NAME,
            ]);

            foreach ($categoryIdentities as $categoryIdentity) {
                if (in_array($categoryIdentity->getAdapterIdentifier(), array_column($this->categories, 'id'), true)) {
                    continue;
                }

                $category = $categoryRepository->find($categoryIdentity->getAdapterIdentifier());

                if (null === $category) {
                    continue;
                }

                $parents = array_reverse(array_filter(explode('|', $category->getPath())));
                $parentCategoryId = array_shift($parents);

                if (!in_array($parentCategoryId, $shopCategories, true)) {
                    continue;
                }

                $this->categories[] = [
                    'id' => $categoryIdentity->getAdapterIdentifier(),
                ];
            }
        }

        return $this->categories;
    }

    private function getSeoCategories(Product $product)
    {
        /**
         * @var CategoryRepository $categoryRepository
         */
        $categoryRepository = $this->entityManager->getRepository(CategoryModel::class);

        /**
         * @var ShopRepository $shopRepository
         */
        $shopRepository = $this->entityManager->getRepository(ShopModel::class);

        $seoCategories = [];
        foreach ($product->getDefaultCategoryIdentifiers() as $categoryIdentifier) {
            $categoryIdentities = $this->identityService->findBy([
                'objectIdentifier' => $categoryIdentifier,
                'objectType' => Category::TYPE,
                'adapterName' => ShopwareAdapter::NAME,
            ]);

            foreach ($categoryIdentities as $categoryIdentity) {
                if (!in_array($categoryIdentity->getAdapterIdentifier(), array_column($this->categories, 'id'), true)) {
                    continue;
                }

                /**
                 * @var null|CategoryModel $category
                 */
                $category = $categoryRepository->find($categoryIdentity->getAdapterIdentifier());

                if (null === $category) {
                    continue;
                }

                $parents = array_reverse(array_filter(explode('|', $category->getPath())));

                /**
                 * @var ShopModel[] $shops
                 */
                $shops = $shopRepository->findBy([
                    'categoryId' => array_shift($parents),
                ]);

                foreach ($shops as $shop) {
                    if (in_array($shop->getId(), array_column($seoCategories, 'shopId'), true)) {
                        continue;
                    }

                    $seoCategories[] = [
                        'categoryId' => $categoryIdentity->getAdapterIdentifier(),
                        'shopId' => $shop->getId(),
                    ];
                }
            }
        }

        return $seoCategories;
    }

    /**
     * @param Product $product
     *
     * @return int
     */
    private function getHighlightFlag(Product $product)
    {
        foreach ($product->getBadges() as $badge) {
            if ($badge->getType() === Badge::TYPE_HIGHLIGHT) {
                return 1;
            }
        }

        return 0;
    }
}
