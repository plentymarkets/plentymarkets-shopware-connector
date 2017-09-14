<?php

namespace ShopwareAdapter\DataProvider\Translation;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PlentyConnector\Connector\TransferObject\Product\Property\Property;
use PlentyConnector\Connector\TransferObject\Product\Property\Value\Value;
use PlentyConnector\Connector\ValueObject\Identity\Identity;
use Shopware\Models\Property\Option as PropertyGroupModel;
use Shopware\Models\Property\Value as PropertyValueModel;
use Shopware\Models\Shop\Shop as ShopModel;

/**
 * Class TranslationDataProvider
 */
class TranslationDataProvider implements TranslationDataProviderInterface
{
    /**
     * @var EntityRepository
     */
    private $shopRepository;

    /**
     * @var EntityRepository
     */
    private $propertyValueRepository;

    /**
     * @var EntityRepository
     */
    private $propertyGroupRepository;

    /**
     * TranslationDataProvider constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->shopRepository = $entityManager->getRepository(ShopModel::class);
        $this->propertyGroupRepository = $entityManager->getRepository(PropertyGroupModel::class);
        $this->propertyValueRepository = $entityManager->getRepository(PropertyValueModel::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getShopsByLocaleIdentitiy(Identity $identity)
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
}
