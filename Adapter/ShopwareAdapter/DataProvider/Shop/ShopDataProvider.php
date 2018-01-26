<?php

namespace ShopwareAdapter\DataProvider\Shop;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Shopware\Models\Shop\Shop as ShopModel;

/**
 * Class TranslationDataProvider
 */
class ShopDataProvider implements ShopDataProviderInterface
{
    /**
     * @var EntityRepository
     */
    private $shopRepository;

    /**
     * TranslationDataProvider constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->shopRepository = $entityManager->getRepository(ShopModel::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultShop()
    {
        return $this->shopRepository->findOneBy(['default' => 1]);
    }
}
