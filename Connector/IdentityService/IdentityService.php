<?php

namespace PlentyConnector\Connector\IdentityService;

use Assert\Assertion;
use PlentyConnector\Connector\IdentityService\Exception\NotFoundException;
use PlentyConnector\Connector\IdentityService\Storage\IdentityStorageInterface;
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
     * IdentityService constructor.
     *
     * @param IdentityStorageInterface $storage
     */
    public function __construct(IdentityStorageInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneOrThrow($adapterIdentifier, $adapterName, $objectType)
    {
        // TODO following line only for debugging!
        return $this->findOneOrCreate($adapterIdentifier, $adapterName, $objectType);

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
            throw new NotFoundException(sprintf('Could not find identity for %s with identifier %s in %s.',
                $objectType, $adapterIdentifier, $adapterName));
        }

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

        return $identity;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria = [])
    {
        Assertion::isArray($criteria);

        return $this->storage->findOneBy($criteria);
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

        $identity = Identity::fromArray($params);

        $result = $this->storage->persist($identity);

        if ($result) {
            //$this->ServiceBus->handle(new IdentityCreatedEvent($identity));
        }

        return $identity;
    }

    /**
     * {@inheritdoc}
     */
    public function findby(array $criteria = [])
    {
        Assertion::isArray($criteria);

        return $this->storage->findBy($criteria);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(Identity $identity)
    {
        $result = $this->storage->remove($identity);

        if ($result) {
            //$this->ServiceBus->handle(new IdentityRemovedEvent($identity));
        }
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
