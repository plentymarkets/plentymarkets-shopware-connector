<?php

namespace ShopwareAdapter\ServiceBus\CommandHandler\Category;

use DeepCopy\DeepCopy;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use InvalidArgumentException;
use PlentyConnector\Connector\IdentityService\Exception\NotFoundException as IdentityNotFoundException;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\ServiceBus\Command\CommandInterface;
use PlentyConnector\Connector\ServiceBus\Command\TransferObjectCommand;
use PlentyConnector\Connector\ServiceBus\CommandHandler\CommandHandlerInterface;
use PlentyConnector\Connector\ServiceBus\CommandType;
use PlentyConnector\Connector\TransferObject\Category\Category;
use PlentyConnector\Connector\TransferObject\Language\Language;
use PlentyConnector\Connector\TransferObject\Media\Media;
use PlentyConnector\Connector\TransferObject\Shop\Shop;
use PlentyConnector\Connector\Translation\TranslationHelperInterface;
use PlentyConnector\Connector\ValueObject\Attribute\Attribute;
use PlentyConnector\Connector\ValueObject\Identity\Identity;
use Shopware\Components\Api\Exception\NotFoundException;
use Shopware\Components\Api\Manager;
use Shopware\Components\Api\Resource\Category as CategoryResource;
use Shopware\Models\Category\Category as CategoryModel;
use Shopware\Models\Category\Repository as CategoryRepository;
use Shopware\Models\Shop\Repository as ShopRepository;
use Shopware\Models\Shop\Shop as ShopModel;
use ShopwareAdapter\DataPersister\Attribute\AttributeDataPersisterInterface;
use ShopwareAdapter\DataPersister\Translation\TranslationDataPersisterInterface;
use ShopwareAdapter\ShopwareAdapter;

class HandleCategoryCommandHandler implements CommandHandlerInterface
{
    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * @var TranslationHelperInterface
     */
    private $translationHelper;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

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
        $this->identityService = $identityService;
        $this->translationHelper = $translationHelper;
        $this->entityManager = $entityManager;
        $this->attributePersister = $attributePersister;
        $this->translationDataPersister = $translationDataPersister;
        $this->categoryRepository = $entityManager->getRepository(CategoryModel::class);
        $this->shopRepository = $entityManager->getRepository(ShopModel::class);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(CommandInterface $command)
    {
        return $command instanceof TransferObjectCommand &&
            $command->getAdapterName() === ShopwareAdapter::NAME &&
            $command->getObjectType() === Category::TYPE &&
            $command->getCommandType() === CommandType::HANDLE;
    }

    /**
     * {@inheritdoc}
     *
     * @var TransferObjectCommand $command
     */
    public function handle(CommandInterface $command)
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

    /**
     * @param Category $category
     * @param Identity $shopIdentity
     */
    private function prepareCategory(Category $category, Identity $shopIdentity)
    {
        $attributes = $category->getAttributes();

        $shopAttribute = array_filter($attributes, function (Attribute $attribute) {
            return 'shopIdentifier' === $attribute->getKey();
        });

        if (!empty($shopAttribute)) {
            throw new InvalidArgumentException('shopIdentifier is not a allowed attribute key');
        }

        $attributes[] = Attribute::fromArray([
            'key' => 'shopIdentifier',
            'value' => $shopIdentity->getAdapterIdentifier(),
        ]);

        $attributes[] = Attribute::fromArray([
            'key' => 'metaRobots',
            'value' => $category->getMetaRobots(),
        ]);

        $category->setAttributes($attributes);
    }

    /**
     * @param Category $category
     * @param Identity $shopIdentity
     *
     * @return null|Identity
     */
    private function handleCategory(Category $category, Identity $shopIdentity)
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

        $this->prepareCategory($category, $shopIdentity);

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
            $mainCategory = $shop->getCategory();

            if (null === $mainCategory) {
                throw new InvalidArgumentException('shop without main cateogry assignment');
            }

            $parentCategory = $mainCategory->getId();
        } else {
            $parentCategoryIdentities = $this->identityService->findBy([
                'objectIdentifier' => (string) $category->getParentIdentifier(),
                'objectType' => Category::TYPE,
                'adapterName' => ShopwareAdapter::NAME,
            ]);

            $possibleIdentities = array_filter($parentCategoryIdentities, function (Identity $identity) use ($shopIdentity) {
                return $this->validIdentity($identity, $shopIdentity);
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

        $possibleIdentities = array_filter($categoryIdentities, function (Identity $identity) use ($shopIdentity) {
            return $this->validIdentity($identity, $shopIdentity);
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
                $categoryIdentity = $this->identityService->create(
                    (string) $category->getIdentifier(),
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
            $categoryModel = $resource->create($params);

            $categoryIdentity = $this->identityService->create(
                (string) $category->getIdentifier(),
                Category::TYPE,
                (string) $categoryModel->getId(),
                ShopwareAdapter::NAME
            );
        } else {
            $categoryModel = $resource->update($categoryIdentity->getAdapterIdentifier(), $params);
        }

        $this->attributePersister->saveCategoryAttributes($categoryModel, $category->getAttributes());
        $this->translationDataPersister->writeCategoryTranslations($category);

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
        $connection = $this->entityManager->getConnection();

        try {
            $query = '
                SELECT categoryID 
                FROM s_categories_attributes 
                WHERE categoryID = :category 
                AND plenty_connector_shop_identifier = :identifier
            ';

            $attribute = $connection->fetchColumn($query, [
                ':category' => $categoryIdentity->getAdapterIdentifier(),
                ':identifier' => $shopIdentity->getAdapterIdentifier(),
            ]);

            return (bool) $attribute;
        } catch (Exception $exception) {
            return false;
        }
    }

    /**
     * @param Category $category
     * @param array    $validIdentities
     */
    private function handleOrphanedCategories(Category $category, array $validIdentities = [])
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

    /**
     * @return CategoryResource
     */
    private function getCategoryResource()
    {
        // without this reset the entitymanager sometimes the album is not found correctly.
        Shopware()->Container()->reset('models');

        return Manager::getResource('Category');
    }
}
