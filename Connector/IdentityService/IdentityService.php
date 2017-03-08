<?php

namespace PlentyConnector\Connector\IdentityService;

use Assert\Assertion;
use PlentyConnector\Connector\IdentityService\Exception\NotFoundException;
use PlentyConnector\Connector\IdentityService\Storage\IdentityStorageInterface;
use PlentyConnector\Connector\ValidatorService\ValidatorServiceInterface;
use PlentyConnector\Connector\ValueObject\Identity\Identity;
use Ramsey\Uuid\Uuid;

/**
 * Class IdentityService.
 */
class IdentityService implements IdentityServiceInterface
{
    /**
     * @var IdentityStorageInterface
     */
    private $storage;

    /**
     * @var ValidatorServiceInterface
     */
    private $validator;

    /**
     * IdentityService constructor.
     *
     * @param IdentityStorageInterface $storage
     * @param ValidatorServiceInterface $validator
     */
    public function __construct(
        IdentityStorageInterface $storage,
        ValidatorServiceInterface $validator
    ) {
        $this->storage = $storage;
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
            throw new NotFoundException(sprintf('Could not find identity for %s with identifier %s in %s.', $objectType, $adapterIdentifier, $adapterName));
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
        $params = compact(
            'objectIdentifier',
            'objectType',
            'adapterIdentifier',
            'adapterName'
        );

        /**
         * @var Identity $identity
         */
        $identity = Identity::fromArray($params);

        $this->storage->persist($identity);
        $this->validator->validate($identity);

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
}
