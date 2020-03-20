<?php

namespace ShopwareAdapter\ServiceBus\CommandHandler\Category;

use DeepCopy\DeepCopy;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use InvalidArgumentException;
use Shopware\Components\Api\Exception\CustomValidationException;
use Shopware\Components\Api\Exception\NotFoundException;
use Shopware\Components\Api\Exception\ParameterMissingException;
use Shopware\Components\Api\Exception\ValidationException;
use Shopware\Components\Api\Manager;
use Shopware\Components\Api\Resource\Category as CategoryResource;
use Shopware\Models\Category\Category as CategoryModel;
use Shopware\Models\Category\Repository as CategoryRepository;
use Shopware\Models\Media\Media as MediaRepository;
use Shopware\Models\Shop\Repository as ShopRepository;
use Shopware\Models\Shop\Shop as ShopModel;
use ShopwareAdapter\DataPersister\Attribute\AttributeDataPersisterInterface;
use ShopwareAdapter\DataPersister\Translation\TranslationDataPersisterInterface;
use ShopwareAdapter\ShopwareAdapter;
use SystemConnector\IdentityService\Exception\NotFoundException as IdentityNotFoundException;
use SystemConnector\IdentityService\IdentityServiceInterface;
use SystemConnector\IdentityService\Struct\Identity;
use SystemConnector\ServiceBus\Command\CommandInterface;
use SystemConnector\ServiceBus\Command\TransferObjectCommand;
use SystemConnector\ServiceBus\CommandHandler\CommandHandlerInterface;
use SystemConnector\ServiceBus\CommandType;
use SystemConnector\TransferObject\Category\Category;
use SystemConnector\TransferObject\Language\Language;
use SystemConnector\TransferObject\Media\Media;
use SystemConnector\TransferObject\Shop\Shop;
use SystemConnector\Translation\TranslationHelperInterface;
use SystemConnector\ValueObject\Attribute\Attribute;

class HandleCategoryCommandHandler implements CommandHandlerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * @var TranslationHelperInterface
     */
    private $translationHelper;

    /**
     * @var AttributeDataPersisterInterface
     */
    private $attributePersister;

    /**
     * @var TranslationDataPersisterInterface
     */
    private $translationDataPersister;

    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    /**
     * @var ShopRepository
     */
    private $shopRepository;

    public function __construct(
        IdentityServiceInterface $identityService,
        TranslationHelperInterface $translationHelper,
        EntityManagerInterface $entityManager,
        AttributeDataPersisterInterface $attributePersister,
        TranslationDataPersisterInterface $translationDataPersister
    ) {
        $this->entityManager = $entityManager;
        $this->identityService = $identityService;
        $this->translationHelper = $translationHelper;
        $this->attributePersister = $attributePersister;
        $this->translationDataPersister = $translationDataPersister;
        $this->categoryRepository = $entityManager->getRepository(CategoryModel::class);
        $this->shopRepository = $entityManager->getRepository(ShopModel::class);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(CommandInterface $command): bool
    {
        return $command instanceof TransferObjectCommand &&
            $command->getAdapterName() === ShopwareAdapter::NAME &&
            $command->getObjectType() === Category::TYPE &&
            $command->getCommandType() === CommandType::HANDLE;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(CommandInterface $command): bool
    {
        /**
         * @var Category $category
         */
        $category = $command->getPayload();

        $validIdentities = [];
        foreach ($category->getShopIdentifiers() as $shopIdentifier) {
            $shopIdentities = $this->identityService->findBy([
                'objectIdentifier' => (string) $shopIdentifier,
                'objectType' => Shop::TYPE,
                'adapterName' => ShopwareAdapter::NAME,
            ]);

            if (empty($shopIdentities)) {
                continue;
            }

            foreach ($shopIdentities as $shopIdentity) {
                $identity = $this->handleCategory($category, $shopIdentity);

                if (null === $identity) {
                    continue;
                }

                $identifier = $identity->getObjectIdentifier();
                $validIdentities[$identifier] = $identifier;
            }
        }

        $this->handleOrphanedCategories($category, $validIdentities);

        return true;
    }

    private function prepareCategory(Category $category): void
    {
        $attributes = $category->getAttributes();

        $attributes[] = Attribute::fromArray([
            'key' => 'metaRobots',
            'value' => $category->getMetaRobots(),
        ]);

        $category->setAttributes($attributes);
    }

    private function handleCategory(Category $category, Identity $shopIdentity): ?Identity
    {
        $deepCopy = new DeepCopy();
        $category = $deepCopy->copy($category);

        /**
         * @var null|ShopModel $shop
         */
        $shop = $this->shopRepository->find($shopIdentity->getAdapterIdentifier());

        if (null === $shop) {
            return null;
        }

        $this->prepareCategory($category);

        $mainCategory = $shop->getCategory();

        if (null === $mainCategory) {
            throw new InvalidArgumentException('shop without main category assignment');
        }

        $shopLocale = $shop->getLocale();

        if (null === $shopLocale) {
            throw new InvalidArgumentException('shop without locale assignment');
        }

        $languageIdentity = $this->identityService->findOneBy([
            'adapterIdentifier' => (string) $shopLocale->getId(),
            'adapterName' => ShopwareAdapter::NAME,
            'objectType' => Language::TYPE,
        ]);

        if (null !== $languageIdentity) {
            /**
             * @var Category $translatedCategory
             */
            $translatedCategory = $this->translationHelper->translate($languageIdentity->getObjectIdentifier(), $category);
            $translatedAttributes = [];

            if (null !== $translatedCategory) {
                foreach ($translatedCategory->getAttributes() as $attribute) {
                    $translatedAttributes[] = $this->translationHelper->translate($languageIdentity->getObjectIdentifier(), $attribute);
                }
                $translatedCategory->setAttributes($translatedAttributes);

                $category = $translatedCategory;
            }
        }

        if (null === $category->getParentIdentifier()) {
            $parentCategory = $mainCategory->getId();
        } else {
            $parentCategoryIdentities = $this->identityService->findBy([
                'objectIdentifier' => (string) $category->getParentIdentifier(),
                'objectType' => Category::TYPE,
                'adapterName' => ShopwareAdapter::NAME,
            ]);

            $possibleIdentities = array_filter($parentCategoryIdentities, function (Identity $identity) use ($mainCategory) {
                return $this->validIdentity($identity, $mainCategory);
            });

            $parentCategoryIdentity = null;
            if (!empty($possibleIdentities)) {
                /**
                 * @var Identity $categoryIdentity
                 */
                $parentCategoryIdentity = array_shift($possibleIdentities);
            }

            if (null === $parentCategoryIdentity) {
                throw new InvalidArgumentException('missing parent category - ' . $category->getParentIdentifier());
            }

            $parentCategory = $parentCategoryIdentity->getAdapterIdentifier();
        }

        $categoryIdentities = $this->identityService->findBy([
            'objectIdentifier' => $category->getIdentifier(),
            'objectType' => Category::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
        ]);

        $possibleIdentities = array_filter($categoryIdentities, function (Identity $identity) use ($mainCategory) {
            return $this->validIdentity($identity, $mainCategory);
        });

        $categoryIdentity = null;
        if (!empty($possibleIdentities)) {
            /**
             * @var Identity $categoryIdentity
             */
            $categoryIdentity = array_shift($possibleIdentities);
        }

        $resource = $this->getCategoryResource();

        if (null !== $categoryIdentity) {
            try {
                $resource->getOne($categoryIdentity->getAdapterIdentifier());
            } catch (NotFoundException $exception) {
                $categoryIdentity = null;
            }
        }

        if (null === $categoryIdentity) {
            $existingCategory = $this->findExistingCategory($category, $parentCategory);

            if (null !== $existingCategory) {
                $categoryIdentity = $this->identityService->insert(
                    $category->getIdentifier(),
                    Category::TYPE,
                    (string) $existingCategory,
                    ShopwareAdapter::NAME
                );
            }
        }

        $params = [
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
                throw new IdentityNotFoundException('media not found - ' . $mediaIdentifier);
            }

            $params['media']['mediaId'] = $mediaIdentity->getAdapterIdentifier();
        }

        if (null !== $categoryIdentity) {
            try {
                $resource->getOne($categoryIdentity->getAdapterIdentifier());
            } catch (NotFoundException $exception) {
                $this->identityService->remove($categoryIdentity);

                $categoryIdentity = null;
            }
        }

        if (null === $categoryIdentity) {
            $categoryModel = $this->createOrUpdateCategory(new CategoryModel(), $params);

            $categoryIdentity = $this->identityService->insert(
                $category->getIdentifier(),
                Category::TYPE,
                (string) $categoryModel->getId(),
                ShopwareAdapter::NAME
            );
        } else {
            /**
             * @var CategoryModel $categoryModel
             */
            $categoryModel = $this->categoryRepository->find($categoryIdentity->getAdapterIdentifier());

            $this->createOrUpdateCategory($categoryModel, $params);
        }

        $this->attributePersister->saveCategoryAttributes($categoryModel, $category->getAttributes());
        $this->translationDataPersister->writeCategoryTranslations($category);

        return $categoryIdentity;
    }

    private function findExistingCategory(Category $category, $parentCategory): ?int
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

    private function validIdentity(Identity $categoryIdentity, CategoryModel $shopMainCategory): bool
    {
        try {
            $existingCategory = $this->categoryRepository->find($categoryIdentity->getAdapterIdentifier());

            if (null === $existingCategory) {
                return false;
            }

            $extractedCategoryPath = array_filter(explode('|', $existingCategory->getPath()));

            if (in_array($shopMainCategory->getId(), $extractedCategoryPath, false)) {
                return true;
            }

            return false;
        } catch (Exception $exception) {
            return false;
        }
    }

    private function handleOrphanedCategories(Category $category, array $validIdentities = []): void
    {
        $categoryIdentities = $this->identityService->findBy([
            'objectIdentifier' => $category->getIdentifier(),
            'objectType' => Category::TYPE,
            'adapterName' => ShopwareAdapter::NAME,
        ]);

        foreach ($categoryIdentities as $identity) {
            $resource = $this->getCategoryResource();

            if (isset($validIdentities[$identity->getObjectIdentifier()])) {
                continue;
            }

            try {
                $resource->getOne($identity->getAdapterIdentifier());
            } catch (NotFoundException $exception) {
                $this->identityService->remove($identity);

                continue;
            }

            $params = [
                'name' => $category->getName(),
                'active' => false,
            ];

            $resource->update($identity->getAdapterIdentifier(), $params);
        }
    }

    private function createOrUpdateCategory(CategoryModel $categoryModel, array $params = []): CategoryModel
    {
        /**
         * @var CategoryModel $parent
         */
        $parent = $this->categoryRepository->find($params['parent']);

        $categoryModel->setActive($params['active']);
        $categoryModel->setPosition($params['position']);
        $categoryModel->setName($params['name']);
        $categoryModel->setParent($parent);
        $categoryModel->setMetaTitle($params['metaTitle']);
        $categoryModel->setMetaKeywords($params['metaKeywords']);
        $categoryModel->setMetaDescription($params['metaDescription']);
        $categoryModel->setCmsHeadline($params['cmsHeadline']);
        $categoryModel->setCmsText($params['cmsText']);

        if (array_key_exists('media', $params)) {
            $mediaRepository = $this->entityManager->getRepository(MediaRepository::class);
            $categoryImage = $mediaRepository->find($params['media']['mediaId']);

            if (null !== $categoryImage) {
                $categoryModel->setMedia($categoryImage);
            }
        }

        $this->entityManager->persist($categoryModel);
        $this->entityManager->flush();

        return $categoryModel;
    }

    private function getCategoryResource(): CategoryResource
    {
        // without this reset the entitymanager sometimes the album is not found correctly.
        Shopware()->Container()->reset('models');

        return Manager::getResource('Category');
    }
}
