<?php

namespace SystemConnector\IdentityService;

use Assert\Assertion;
use Ramsey\Uuid\Uuid;
use SystemConnector\IdentityService\Exception\NotFoundException;
use SystemConnector\IdentityService\Storage\IdentityServiceStorageInterface;
use SystemConnector\ValidatorService\ValidatorServiceInterface;
use SystemConnector\ValueObject\Identity\Identity;
use Traversable;

class IdentityService implements IdentityServiceInterface
{
    /**
     * @var IdentityServiceStorageInterface[]
     */
    private $storages;

    /**
     * @var ValidatorServiceInterface
     */
    private $validator;

    public function __construct(
        Traversable $storage,
        ValidatorServiceInterface $validator
    ) {
        $this->storages = iterator_to_array($storage);
        $this->validator = $validator;
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

            $identity = $this->insert(
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

        $storage = reset($this->storages);
        $identity = $storage->findOneBy($criteria);

        if ($identity !== null) {
            $this->validator->validate($identity);

            return $identity;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function insert($objectIdentifier, $objectType, $adapterIdentifier, $adapterName)
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

        $storage = reset($this->storages);
        $storage->insert($identity);

        return $identity;
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(array $criteria = [])
    {
        Assertion::isArray($criteria);

        $storage = reset($this->storages);
        $identities = $storage->findBy($criteria);

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

        $storage = reset($this->storages);
        $storage->update($identity, $params);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(Identity $identity)
    {
        $this->validator->validate($identity);

        $storage = reset($this->storages);
        $storage->remove($identity);
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
