<?php

namespace ShopwareAdapter\ServiceBus\CommandHandler\Category;

use Doctrine\ORM\EntityManagerInterface;
use PlentyConnector\Adapter\ShopwareAdapter\Helper\AttributeHelper;
use PlentyConnector\Connector\IdentityService\Exception\NotFoundException as IdentityNotFoundException;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\ServiceBus\Command\Category\HandleCategoryCommand;
use PlentyConnector\Connector\ServiceBus\Command\CommandInterface;
use PlentyConnector\Connector\ServiceBus\Command\HandleCommandInterface;
use PlentyConnector\Connector\ServiceBus\CommandHandler\CommandHandlerInterface;
use PlentyConnector\Connector\TransferObject\Category\Category;
use PlentyConnector\Connector\TransferObject\Language\Language;
use PlentyConnector\Connector\TransferObject\Media\Media;
use PlentyConnector\Connector\TransferObject\Shop\Shop;
use PlentyConnector\Connector\Translation\TranslationHelperInterface;
use PlentyConnector\Connector\ValueObject\Attribute\Attribute;
use PlentyConnector\Connector\ValueObject\Identity\Identity;
use Psr\Log\LoggerInterface;
use Shopware\Components\Api\Exception\NotFoundException;
use Shopware\Components\Api\Resource\Category as CategoryResource;
use Shopware\Models\Category\Category as CategoryModel;
use Shopware\Models\Category\Repository as CategoryRepository;
use Shopware\Models\Shop\Repository as ShopRepository;
use Shopware\Models\Shop\Shop as ShopModel;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class HandleCategoryCommandHandler.
 */
class HandleCategoryCommandHandler implements CommandHandlerInterface
{
    /**
     * @var CategoryResource
     */
    private $resource;

    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * @var TranslationHelperInterface
     */
    private $translationHelper;

    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    /**
     * @var ShopRepository
     */
    private $shopRepository;

    /**
     * @var AttributeHelper
     */
    private $attributeHelper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * HandleCategoryCommandHandler constructor.
     *
     * @param CategoryResource           $resource
     * @param IdentityServiceInterface   $identityService
     * @param TranslationHelperInterface $translationHelper
     * @param EntityManagerInterface     $entityManager
     * @param AttributeHelper            $attributeHelper
     * @param LoggerInterface            $logger
     */
    public function __construct(
        CategoryResource $resource,
        IdentityServiceInterface $identityService,
        TranslationHelperInterface $translationHelper,
        EntityManagerInterface $entityManager,
        AttributeHelper $attributeHelper,
        LoggerInterface $logger
    ) {
        $this->resource = $resource;
        $this->identityService = $identityService;
        $this->translationHelper = $translationHelper;
        $this->categoryRepository = $entityManager->getRepository(CategoryModel::class);
        $this->shopRepository = $entityManager->getRepository(ShopModel::class);
        $this->attributeHelper = $attributeHelper;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(CommandInterface $command)
    {
        return $command instanceof HandleCategoryCommand &&
            $command->getAdapterName() === ShopwareAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(CommandInterface $command)
    {
        /**
         * @var HandleCommandInterface $command
         * @var Category               $category
         */
        $category = $command->getTransferObject();

        $validIdentities = [];
        foreach ($category->getShopIdentifiers() as $shopIdentifier) {
            try {
                $identity = $this->handleCategory($category, $shopIdentifier);

                if (null === $identity) {
                    continue;
                }

                $validIdentities[$identity->getObjectIdentifier()] = $identity->getObjectIdentifier();
            } catch (IdentityNotFoundException $exception) {
                $this->logger->warning($exception->getMessage());
            }
        }

        $this->handleOrphanedCategories($category, $validIdentities);
    }

    /**
     * @param Category $category
     * @param Identity $shopIdentity
     *
     * @throws \InvalidArgumentException
     */
    private function prepareCategory(Category $category, Identity $shopIdentity)
    {
        $attributes = $category->getAttributes();

        $shopAttribute = array_filter($attributes, function (Attribute $attribute) {
            return 'shopIdentifier' === $attribute->getKey();
        });

        if (!empty($shopAttribute)) {
            throw new \InvalidArgumentException('shopIdentifier is not a allowed attribute key');
        }

        $attributes[] = Attribute::fromArray([
            'key' => 'shopIdentifier',
            'value' => $shopIdentity->getObjectIdentifier(),
        ]);

        $attributes[] = Attribute::fromArray([
            'key' => 'metaRobots',
            'value' => $category->getMetaRobots(),
        ]);

        $category->setAttributes($attributes);
    }

    /**
     * @param Category $category
     * @param $shopIdentifier
     *
     * @throws NotFoundException
     * @throws IdentityNotFoundException
     *
     * @return null|Identity
     */
    private function handleCategory(Category $category, $shopIdentifier)
    {
        $shopIdentity = $this->identityService->findOneBy([
            'objectIdentifier' => (string) $shopIdentifier,
            'objectType' => Shop::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
        ]);

        if (null === $shopIdentity) {
            return null;
        }

        $shop = $this->shopRepository->find($shopIdentity->getAdapterIdentifier());

        if (null === $shop) {
            throw new IdentityNotFoundException('missing shop - ' . $shopIdentity->getObjectIdentifier());
        }

        $category = clone $category;

        $this->prepareCategory($category, $shopIdentity);

        $languageIdentity = $this->identityService->findOneBy([
            'adapterIdentifier' => (string) $shop->getLocale()->getId(),
            'adapterName' => ShopwareAdapter::NAME,
            'objectType' => Language::TYPE,
        ]);

        if (null !== $languageIdentity) {
            $category = $this->translationHelper->translate($languageIdentity->getObjectIdentifier(), $category);
        }

        if (null === $category->getParentIdentifier()) {
            $parentCategory = $shop->getCategory()->getId();
        } else {
            $parentCategoryIdentity = $this->identityService->findOneBy([
                'objectIdentifier' => (string) $category->getParentIdentifier(),
                'objectType' => Category::TYPE,
                'adapterName' => ShopwareAdapter::NAME,
            ]);

            if (null === $parentCategoryIdentity) {
                throw new \InvalidArgumentException('missing parent category - ' . $category->getParentIdentifier());
            }

            $parentCategory = $parentCategoryIdentity->getAdapterIdentifier();
        }

        $categoryIdentities = $this->identityService->findBy([
            'objectIdentifier' => $category->getIdentifier(),
            'objectType' => Category::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
        ]);

        $possibleIdentities = array_filter($categoryIdentities, function (Identity $identity) use ($shopIdentity) {
            return $this->validIdentity($identity, $shopIdentity);
        });

        $categoryIdentity = null;
        if (!empty($possibleIdentities)) {
            $categoryIdentity = array_shift($possibleIdentities);
        }

        if (null === $categoryIdentity) {
            $existingCategory = $this->findExistingCategory($category, $parentCategory);

            if (null !== $existingCategory) {
                $categoryIdentity = $this->identityService->create(
                    (string) $category->getIdentifier(),
                    Category::TYPE,
                    (string) $existingCategory,
                    ShopwareAdapter::NAME
                );
            }
        }

        $parans = [
            'active' => $category->getActive(),
            'position' => $category->getPosition(),
            'name' => $category->getName(),
            'parent' => $parentCategory,
            'metaTitle' => $category->getMetaTitle(),
            'metaKeywords' => $category->getMetaKeywords(),
            'metaDescription' => $category->getMetaDescription(),
            'cmsHeadline' => $category->getDescription(),
            'cmsText' => $category->getLongDescription(),
        ];

        if (!empty($category->getImageIdentifiers())) {
            $mediaIdentifiers = $category->getImageIdentifiers();
            $mediaIdentifier = array_shift($mediaIdentifiers);

            $mediaIdentity = $this->identityService->findOneBy([
                'objectIdentifier' => (string) $mediaIdentifier,
                'objectType' => Media::TYPE,
                'adapterName' => ShopwareAdapter::NAME,
            ]);

            if (null === $mediaIdentity) {
                throw new NotFoundException();
            }

            $parans['media']['mediaId'] = $mediaIdentity->getAdapterIdentifier();
        }

        if (null !== $categoryIdentity) {
            try {
                $this->resource->getOne($categoryIdentity->getAdapterIdentifier());
            } catch (NotFoundException $exception) {
                $this->identityService->remove($categoryIdentity);

                $categoryIdentity = null;
            }
        }

        if (null === $categoryIdentity) {
            $newCategory = $this->resource->create($parans);

            $categoryIdentity = $this->identityService->create(
                (string) $category->getIdentifier(),
                Category::TYPE,
                (string) $newCategory->getId(),
                ShopwareAdapter::NAME
            );
        } else {
            $this->resource->update($categoryIdentity->getAdapterIdentifier(), $parans);
        }

        $this->attributeHelper->saveAttributes(
            (int) $categoryIdentity->getAdapterIdentifier(),
            $category->getAttributes(),
            's_categories_attributes'
        );

        return $categoryIdentity;
    }

    /**
     * @param Category $category
     * @param int      $parentCategory
     *
     * @return null|int
     */
    private function findExistingCategory(Category $category, $parentCategory)
    {
        $existingCategory = $this->categoryRepository->findOneBy([
            'name' => $category->getName(),
            'parentId' => $parentCategory,
        ]);

        if (null === $existingCategory) {
            return null;
        }

        return $existingCategory->getId();
    }

    /**
     * @param Identity $categoryIdentity
     * @param Identity $shopIdentity
     *
     * @return bool
     */
    private function validIdentity(Identity $categoryIdentity, Identity $shopIdentity)
    {
        $id = $categoryIdentity->getAdapterIdentifier();

        $categoryQuery = $this->categoryRepository->getDetailQueryBuilderWithoutArticles($id)->getQuery();

        $categories = $categoryQuery->execute();

        if (empty($categories)) {
            return false;
        }

        /**
         * @var CategoryModel $category
         */
        $category = array_shift($categories);

        $attributes = $category->getAttribute();

        if (method_exists($attributes, 'getPlentyConnectorShopIdentifier')) {
            return $shopIdentity->getObjectIdentifier() === $attributes->getPlentyConnectorShopIdentifier();
        }

        return false;
    }

    /**
     * @param Category $category
     * @param array $validIdentities
     */
    private function handleOrphanedCategories(Category $category, array $validIdentities = [])
    {
        $categoryIdentities = $this->identityService->findBy([
            'objectIdentifier' => $category->getIdentifier(),
            'objectType' => Category::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
        ]);

        foreach ($categoryIdentities as $identity) {
            if (isset($validIdentities[$identity->getObjectIdentifier()])) {
                continue;
            }

            try {
                $this->resource->getOne($identity->getAdapterIdentifier());
            } catch (NotFoundException $exception) {
                $this->identityService->remove($identity);

                continue;
            }

            $params = [
                'name' => $category->getName(),
                'active' => false,
            ];

            $this->resource->update($identity->getAdapterIdentifier(), $params);
        }
    }
}
