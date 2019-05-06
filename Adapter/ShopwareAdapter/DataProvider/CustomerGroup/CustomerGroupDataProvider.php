<?php

namespace ShopwareAdapter\DataProvider\CustomerGroup;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Shopware\Models\Customer\Group;

class CustomerGroupDataProvider implements CustomerGroupDataProviderInterface
{
    /**
     * @var EntityRepository
     */
    private $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(Group::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerGroupKeyByShopwareIdentifier($identifier)
    {
        /**
         * @var null|Group $group
         */
        $group = $this->repository->find($identifier);

        if (null === $group) {
            return null;
        }

        return $group->getKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerGroupByShopwareIdentifier($identifier)
    {
        /**
         * @var null|Group $group
         */
        $group = $this->repository->find($identifier);

        if (null === $group) {
            return null;
        }

        return $group;
    }
}
