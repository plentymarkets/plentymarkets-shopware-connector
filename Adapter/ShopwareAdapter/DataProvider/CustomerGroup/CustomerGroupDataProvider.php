<?php

namespace ShopwareAdapter\DataProvider\CustomerGroup;

use Doctrine\ORM\EntityManagerInterface;
use Shopware\Components\Model\ModelRepository;
use Shopware\Models\Customer\Group;

/**
 * Class CustomerGroupDataProvider
 */
class CustomerGroupDataProvider implements CustomerGroupDataProviderInterface
{
    /**
     * @var ModelRepository
     */
    private $repository;

    /**
     * CustomerGroupDataProvider constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(Group::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerGroupKeyByShopwareIdentifier($identifier)
    {
        $group = $this->repository->find($identifier);

        if (null === $group) {
            return null;
        }

        return $group->getKey();
    }
}
