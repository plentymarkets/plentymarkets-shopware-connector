<?php

namespace ShopwareAdapter\ServiceBus\CommandHandler\Product;

use Doctrine\ORM\EntityManagerInterface;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\ServiceBus\Command\CommandInterface;
use PlentyConnector\Connector\ServiceBus\Command\HandleCommandInterface;
use PlentyConnector\Connector\ServiceBus\Command\Product\HandleProductCommand;
use PlentyConnector\Connector\ServiceBus\CommandHandler\CommandHandlerInterface;
use PlentyConnector\Connector\TransferObject\Category\Category;
use PlentyConnector\Connector\TransferObject\Manufacturer\Manufacturer;
use PlentyConnector\Connector\TransferObject\Media\Media;
use PlentyConnector\Connector\TransferObject\Product\LinkedProduct\LinkedProduct;
use PlentyConnector\Connector\TransferObject\Product\Product;
use PlentyConnector\Connector\TransferObject\ShippingProfile\ShippingProfile;
use PlentyConnector\Connector\TransferObject\Shop\Shop;
use PlentyConnector\Connector\TransferObject\VatRate\VatRate;
use PlentyConnector\Connector\ValueObject\Attribute\Attribute;
use Psr\Log\LoggerInterface;
use Shopware\Components\Api\Resource\Article;
use Shopware\Models\Category\Category as CategoryModel;
use Shopware\Models\Property\Group as GroupModel;
use Shopware\Models\Property\Repository;
use Shopware\Models\Shop\Shop as ShopModel;
use ShopwareAdapter\DataPersister\Attribute\AttributeDataPersisterInterface;
use ShopwareAdapter\DataPersister\Translation\TranslationDataPersisterInterface;
use ShopwareAdapter\Helper\AttributeHelper;
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
     * @var AttributeDataPersisterInterface
     */
    private $attributeDataPersister;

    /**
     * @var TranslationDataPersisterInterface
     */
    private $translationDataPersister;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var Article
     */
    private $resource;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * HandleProductCommandHandler constructor.
     *
     * @param IdentityServiceInterface          $identityService
     * @param AttributeHelper                   $attributeHelper
     * @param AttributeDataPersisterInterface   $attributeDataPersister
     * @param TranslationDataPersisterInterface $translationDataPersister
     * @param EntityManagerInterface            $entityManager
     * @param Article                           $resource
     * @param LoggerInterface                   $logger
     */
    public function __construct(
        IdentityServiceInterface $identityService,
        AttributeHelper $attributeHelper,
        AttributeDataPersisterInterface $attributeDataPersister,
        TranslationDataPersisterInterface $translationDataPersister,
        EntityManagerInterface $entityManager,
        Article $resource,
        LoggerInterface $logger
    ) {
        $this->identityService = $identityService;
        $this->attributeHelper = $attributeHelper;
        $this->attributeDataPersister = $attributeDataPersister;
        $this->translationDataPersister = $translationDataPersister;
        $this->entityManager = $entityManager;
        $this->resource = $resource;
        $this->logger = $logger;
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
     */
    public function handle(CommandInterface $command)
    {
        /**
         * @var HandleCommandInterface $command
         * @var Product                $product
         */
        $product = $command->getTransferObject();

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
            $this->logger->notice('vat rate not mapped - ' . $product->getVatRateIdentifier(), ['command' => $command]);

            return false;
        }

        $manufacturerIdentity = $this->identityService->findOneBy([
            'adapterName' => ShopwareAdapter::NAME,
            'objectType' => Manufacturer::TYPE,
            'objectIdentifier' => $product->getManufacturerIdentifier(),
        ]);

        if (null === $manufacturerIdentity) {
            $this->logger->error('manufacturer is missing - ' . $product->getManufacturerIdentifier(),
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
                $this->logger->notice('image is missing - ' . $image->getMediaIdentifier(), ['command' => $command]);

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

        $categoryRepository = $this->entityManager->getRepository(CategoryModel::class);
        $shopRepository = $this->entityManager->getRepository(ShopModel::class);

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

                /**
                 * @var ShopModel[] $shops
                 */
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
                continue;
            }

            $attributes = $product->getAttributes();

            $existingAttributes = array_filter($attributes, function (Attribute $attribute) use ($profileIdentity) {
                return $attribute->getKey() === 'shippingProfile' . $profileIdentity->getAdapterIdentifier();
            });

            if (!empty($existingAttributes)) {
                $this->logger->notice('shippingProfile is not a allowed attribute key', ['command' => $command]);

                continue;
            }

            $attributes[] = Attribute::fromArray([
                'key' => 'shippingProfile' . $profileIdentity->getAdapterIdentifier(),
                'value' => $profileIdentity->getObjectIdentifier(),
            ]);

            $product->setAttributes($attributes);
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
            'similar' => $this->getLinkedProducts($product),
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

        $createProduct = false;

        if (null === $identity) {
            try {
                $existingProduct = $this->resource->getIdFromNumber($product->getNumber());

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
            if (!$this->resource->getIdByData(['id' => $identity->getAdapterIdentifier()])) {
                $this->identityService->remove($identity);

                $createProduct = true;
            }
        }

        $this->attributeHelper->addFieldAsAttribute($product, 'technicalDescription');

        if ($createProduct) {
            $productModel = $this->resource->create($params);

            $this->identityService->create(
                $product->getIdentifier(),
                Product::TYPE,
                (string) $productModel->getId(),
                ShopwareAdapter::NAME
            );
        } else {
            $productModel = $this->resource->update($identity->getAdapterIdentifier(), $params);
        }

        foreach ($productModel->getDetails() as $detail) {
            $this->attributeDataPersister->saveProductDetailAttributes(
                $detail,
                $product->getAttributes()
            );
        }

        $this->translationDataPersister->writeProductTranslations($product);

        return true;
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

                $productExists = $this->resource->getIdByData(['id' => $productIdentity->getAdapterIdentifier()]);
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
         * @var Repository $groupRepository
         */
        $groupRepository = $this->entityManager->getRepository(GroupModel::class);

        /**
         * @var Group $propertyGroup
         */
        $propertyGroup = $groupRepository->findOneBy(['name' => 'PlentyConnector']);

        if (null === $propertyGroup) {
            $propertyGroup = new GroupModel();
            $propertyGroup->setName('PlentyConnector');
            $propertyGroup->setPosition(1);
            $propertyGroup->setComparable(true);
            $propertyGroup->setSortMode(true);

            $this->entityManager->persist($propertyGroup);
            $this->entityManager->flush();
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
}
