<?php

namespace PlentyConnector\Connector\IdentityService\Storage;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PlentyConnector\Connector\IdentityService\Model\Identity as IdentityModel;
use PlentyConnector\Connector\ValueObject\Identity\Identity;

/**
 * Class DoctrineIdentityStorage.
 */
class DoctrineIdentityStorage implements IdentityStorageInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var EntityRepository
     */
    private $identityRepository;

    /**
     * DoctrineIdentityStorage constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->identityRepository = $this->entityManager->getRepository(IdentityModel::class);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria = [])
    {
        $identity = null;

        /**
         * @var IdentityModel
         */
        $result = $this->identityRepository->findOneBy($criteria);

        if (null !== $result) {
            $identity = Identity::fromArray([
                'objectIdentifier' => $result->getObjectIdentifier(),
                'objectType' => $result->getObjectType(),
                'adapterIdentifier' => $result->getAdapterIdentifier(),
                'adapterName' => $result->getAdapterName(),
            ]);
        }

        return $identity;
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(array $criteria = [])
    {
        /**
         * @var IdentityModel[]
         */
        $result = $this->identityRepository->findBy($criteria);

        return array_map(function (IdentityModel $model) {
            return Identity::fromArray([
                'objectIdentifier' => $model->getObjectIdentifier(),
                'objectType' => $model->getObjectType(),
                'adapterIdentifier' => $model->getAdapterIdentifier(),
                'adapterName' => $model->getAdapterName(),
            ]);
        }, $result);
    }

    /**
     * {@inheritdoc}
     */
    public function persist(Identity $identity)
    {
        $model = new IdentityModel(
            $identity->getObjectIdentifier(),
            $identity->getObjectType(),
            $identity->getAdapterIdentifier(),
            $identity->getAdapterName()
        );

        $this->entityManager->persist($model);
        $this->entityManager->flush();
        $this->entityManager->clear();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function remove(Identity $identity)
    {
        $result = $this->identityRepository->findOneBy([
            'adapterIdentifier' => $identity->getAdapterIdentifier(),
            'adapterName' => $identity->getAdapterName(),
            'objectIdentifier' => $identity->getObjectIdentifier(),
            'objectType' => $identity->getObjectType(),
        ]);

        if (null === $result) {
            return false;
        }

        $this->entityManager->remove($result);
        $this->entityManager->flush();
        $this->entityManager->clear();

        return true;
    }
}
