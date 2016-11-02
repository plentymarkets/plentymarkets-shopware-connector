<?php

namespace PlentyConnector\Connector\Identity\Storage;

use Doctrine\ORM\EntityManagerInterface;
use PlentyConnector\Connector\Identity\Model\Identity as IdentityModel;
use PlentyConnector\Connector\Identity\Model\IdentityRepository;
use PlentyConnector\Connector\TransferObject\Identity\IdentityInterface;

/**
 * Class DoctrineIdentityStorage
 *
 * @package PlentyConnector\Connector\Identity\Storage
 */
class DoctrineIdentityStorage implements IdentityStorageInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var IdentityRepository
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
     * @param array $criteria
     *
     * @return IdentityInterface|null
     */
    public function findBy(array $criteria = [])
    {
        /**
         * @var IdentityModel $result
         */
        $result = $this->identityRepository->findOneBy($criteria);

        $identity = null;

        if (null !== $result) {
            $identity = Identity::fromArray([
                'objectIdentifier' => $result->getObjectIdentifier(),
                'objectType' => $result->getObjectType(),
                'adapterIdentifier' => $result->getAdapterIdentifier(),
                'adapterName' => $result->getAdapterName()
            ]);
        }

        return $identity;
    }

    /**
     * @param IdentityInterface $identity
     */
    public function persist(IdentityInterface $identity)
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
     * @inheritdoc
     */
    public function remove($adapterIdentifier, $adapterName)
    {
        $result = $this->identityRepository->findBy([
            'adapterIdentifier' => $adapterIdentifier,
            'adapterName' => $adapterName,
        ]);

        foreach ($result as $identity) {
            $this->entityManager->remove($identity);
        }

        $this->entityManager->flush();
    }
}
