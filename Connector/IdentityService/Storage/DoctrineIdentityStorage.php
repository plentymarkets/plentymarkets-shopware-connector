<?php

namespace SystemConnector\IdentityService\Storage;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use RuntimeException;
use SystemConnector\IdentityService\Model\Identity as IdentityModel;
use SystemConnector\ValueObject\Identity\Identity;

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
        /**
         * @var null|IdentityModel
         */
        $result = $this->identityRepository->findOneBy($criteria);

        if (null !== $result) {
            return Identity::fromArray([
                'objectIdentifier' => $result->getObjectIdentifier(),
                'objectType' => $result->getObjectType(),
                'adapterIdentifier' => $result->getAdapterIdentifier(),
                'adapterName' => $result->getAdapterName(),
            ]);
        }

        return null;
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
    }

    /**
     * {@inheritdoc}
     */
    public function update(Identity $identity, array $params = [])
    {
        $model = $this->identityRepository->findOneBy([
            'objectIdentifier' => $identity->getObjectIdentifier(),
            'objectType' => $identity->getObjectType(),
            'adapterIdentifier' => $identity->getAdapterIdentifier(),
            'adapterName' => $identity->getAdapterName(),
        ]);

        if (null === $model) {
            throw new RuntimeException('could not find identity for update');
        }

        if (!empty($params['objectIdentifier'])) {
            $model->setObjectIdentifier($params['objectIdentifier']);
        }
        if (!empty($params['objectType'])) {
            $model->setAdapterName($params['objectType']);
        }
        if (!empty($params['adapterIdentifier'])) {
            $model->setAdapterIdentifier($params['adapterIdentifier']);
        }
        if (!empty($params['adapterName'])) {
            $model->setObjectType($params['adapterName']);
        }

        $this->entityManager->persist($model);
        $this->entityManager->flush();

        return Identity::fromArray([
            'objectIdentifier' => $model->getObjectIdentifier(),
            'objectType' => $model->getObjectType(),
            'adapterIdentifier' => $model->getAdapterIdentifier(),
            'adapterName' => $model->getAdapterName(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(Identity $identity)
    {
        $model = $this->identityRepository->findOneBy([
            'adapterIdentifier' => $identity->getAdapterIdentifier(),
            'adapterName' => $identity->getAdapterName(),
            'objectIdentifier' => $identity->getObjectIdentifier(),
            'objectType' => $identity->getObjectType(),
        ]);

        if (null === $model) {
            return;
        }

        $this->entityManager->remove($model);
        $this->entityManager->flush();
    }
}
