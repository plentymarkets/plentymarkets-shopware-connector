<?php

namespace ShopwareAdapter\DataProvider\Translation;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Shopware\Models\Article\Configurator\Group;
use Shopware\Models\Article\Configurator\Option;
use Shopware\Models\Article\Image;
use Shopware\Models\Property\Option as PropertyGroupModel;
use Shopware\Models\Property\Value as PropertyValueModel;
use Shopware\Models\Shop\Shop;
use Shopware\Models\Shop\Shop as ShopModel;
use SystemConnector\IdentityService\Struct\Identity;
use SystemConnector\TransferObject\Product\Property\Property;
use SystemConnector\TransferObject\Product\Property\Value\Value;

class TranslationDataProvider implements TranslationDataProviderInterface
{
    /**
     * @var EntityRepository
     */
    private $shopRepository;

    /**
     * @var EntityRepository
     */
    private $propertyGroupRepository;

    /**
     * @var EntityRepository
     */
    private $propertyValueRepository;

    /**
     * @var EntityRepository
     */
    private $configurationGroupRepository;

    /**
     * @var EntityRepository
     */
    private $configurationValueRepository;

    /**
     * @var EntityRepository
     */
    private $articleImageRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->shopRepository = $entityManager->getRepository(ShopModel::class);
        $this->propertyGroupRepository = $entityManager->getRepository(PropertyGroupModel::class);
        $this->propertyValueRepository = $entityManager->getRepository(PropertyValueModel::class);
        $this->configurationValueRepository = $entityManager->getRepository(Option::class);
        $this->configurationGroupRepository = $entityManager->getRepository(Group::class);
        $this->articleImageRepository = $entityManager->getRepository(Image::class);
    }

    /**
     * @param Identity $identity
     *
     * @return Shop[]
     */
    public function getShopsByLocaleIdentity(Identity $identity): array
    {
        return $this->shopRepository->findBy([
            'locale' => $identity->getAdapterIdentifier(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getPropertyOptionByName(Property $property)
    {
        return $this->propertyGroupRepository->findOneBy([
            'name' => $property->getName(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getPropertyValueByValue(Value $value)
    {
        return $this->propertyValueRepository->findOneBy([
            'value' => $value->getValue(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationGroupByName(Property $property)
    {
        return $this->configurationGroupRepository->findOneBy([
            'name' => $property->getName(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationOptionByName(Value $value)
    {
        return $this->configurationValueRepository->findOneBy([
            'name' => $value->getValue(),
        ]);
    }

    /**
     * @param Identity $mediaIdentity
     * @param $articleId
     * @return null|Image
     */
    public function getArticleImage(Identity $mediaIdentity, $articleId)
    {
        return $this->articleImageRepository->findOneBy([
            'articleId' => $articleId,
            'media' => $mediaIdentity->getAdapterIdentifier(),
        ]);
    }
}
