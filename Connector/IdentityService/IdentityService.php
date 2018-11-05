<?php

namespace SystemConnector\IdentityService;

use Assert\Assertion;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use SystemConnector\IdentityService\Exception\NotFoundException;
use SystemConnector\IdentityService\Storage\IdentityServiceStorageInterface;
use SystemConnector\ValidatorService\ValidatorServiceInterface;
use SystemConnector\ValueObject\Identity\Identity;

class IdentityService implements IdentityServiceInterface
{
    /**
     * @var IdentityServiceStorageInterface
     */
    private $storage;

    /**
     * @var ValidatorServiceInterface
     */
    private $validator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        IdentityServiceStorageInterface $storage,
        ValidatorServiceInterface $validator,
        LoggerInterface $logger
    ) {
        $this->storage = $storage;
        $this->validator = $validator;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneOrThrow($adapterIdentifier, $adapterName, $objectType)
    {
        Assertion::string($adapterIdentifier);
        Assertion::notBlank($adapterIdentifier);
        Assertion::string($adapterName);
        Assertion::notBlank($adapterName);
        Assertion::string($objectType);
        Assertion::notBlank($objectType);

        $identity = $this->findOneBy([
            'objectType' => $objectType,
            'adapterIdentifier' => $adapterIdentifier,
            'adapterName' => $adapterName,
        ]);

        if (null === $identity) {
            throw new NotFoundException(printf(
                'Could not find identity for %s with identifier %s in %s.',
                $objectType,
                $adapterIdentifier,
                $adapterName
            ));
        }

        $this->validator->validate($identity);

        return $identity;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneOrCreate($adapterIdentifier, $adapterName, $objectType)
    {
        Assertion::string($adapterIdentifier);
        Assertion::notBlank($adapterIdentifier);
        Assertion::string($adapterName);
        Assertion::notBlank($adapterName);
        Assertion::string($objectType);
        Assertion::notBlank($objectType);

        $identity = $this->findOneBy([
            'objectType' => $objectType,
            'adapterIdentifier' => $adapterIdentifier,
            'adapterName' => $adapterName,
        ]);

        if (null === $identity) {
            $objectIdentifier = Uuid::uuid4()->toString();

            $identity = $this->create(
                $objectIdentifier,
                $objectType,
                (string) $adapterIdentifier,
                $adapterName
            );
        }

        $this->validator->validate($identity);

        return $identity;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria = [])
    {
        Assertion::isArray($criteria);

        $identity = $this->storage->findOneBy($criteria);
        $this->validator->validate($identity);

        return $identity;
    }

    /**
     * {@inheritdoc}
     */
    public function create($objectIdentifier, $objectType, $adapterIdentifier, $adapterName)
    {
        /**
         * @var Identity $identity
         */
        $identity = Identity::fromArray([
            'objectIdentifier' => $objectIdentifier,
            'objectType' => $objectType,
            'adapterIdentifier' => $adapterIdentifier,
            'adapterName' => $adapterName,
        ]);

        $this->validator->validate($identity);
        $this->storage->persist($identity);

        return $identity;
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(array $criteria = [])
    {
        Assertion::isArray($criteria);

        $identities = $this->storage->findBy($criteria);

        array_walk($identities, function (Identity $identity) {
            $this->validator->validate($identity);
        });

        return $identities;
    }

    /**
     * {@inheritdoc}
     */
    public function update(Identity $identity, array $params = [])
    {
        $this->validator->validate($identity);
        $this->storage->update($identity, $params);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(Identity $identity)
    {
        $this->validator->validate($identity);
        $this->storage->remove($identity);
    }

    /**
     * {@inheritdoc}
     */
    public function exists(array $criteria = [])
    {
        $identity = $this->findOneBy($criteria);

        return (bool) $identity;
    }

    /**
     * {@inheritdoc}
     */
    public function isMappedIdentity($objectIdentifier, $objectType, $adapterName)
    {
        $identities = $this->findBy([
            'objectIdentifier' => $objectIdentifier,
            'objectType' => $objectType,
        ]);

        $otherIdentities = array_filter($identities, function (Identity $identity) use ($adapterName) {
            return $identity->getAdapterName() !== $adapterName;
        });

        if (empty($otherIdentities)) {
            return false;
        }

        return true;
    }
}
