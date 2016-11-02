<?php

namespace PlentyConnector\Connector\Identity;

use Assert\Assertion;
use PlentyConnector\Connector\Identity\Storage\IdentityStorageInterface;

/**
 * Class IdentityService
 *
 * @package PlentyConnector\Connector\Identity
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
     * @inheritdoc
     */
    public function findOrCreateIdentity($adapterIdentifier, $adapterName, $objectType)
    {
        Assertion::string($adapterIdentifier);
        Assertion::string($adapterName);
        Assertion::string($objectType);

        $Identity = $this->findIdentity([
            'objectType' => $objectType,
            'adapterIdentifier' => (string)$adapterIdentifier,
            'adapterName' => $adapterName
        ]);

        if (null === $Identity) {
            $objectIdentifier = Uuid::uuid4()->toString();

            $Identity = $this->createIdentity(
                $objectIdentifier,
                $objectType,
                (string)$adapterIdentifier,
                $adapterName
            );
        }

        return $Identity;
    }

    /**
     * @inheritdoc
     */
    public function findIdentity(array $criteria = [])
    {
        Assertion::isArray($criteria);
        Assertion::allInArray($criteria, [
            'objectIdentifier',
            'objectType',
            'adapterIdentifier',
            'adapterName'
        ]);

        return $this->storage->findBy($criteria);
    }

    /**
     * @inheritdoc
     */
    public function createIdentity($objectIdentifier, $objectType, $adapterIdentifier, $adapterName)
    {
        Assertion::string($objectIdentifier);
        Assertion::string($objectType);
        Assertion::string($adapterIdentifier);
        Assertion::string($adapterName);

        $params = compact(
            'objectIdentifier',
            'objectType',
            'adapterIdentifier',
            'adapterName'
        );

        $Identity = Identity::fromArray($params);

        $this->storage->persist($Identity);

        return $Identity;
    }

    /**
     * @param $adapterIdentifier
     * @param $adapterName
     */
    public function removeIdentity($adapterIdentifier, $adapterName)
    {
        Assertion::string($adapterIdentifier);
        Assertion::string($adapterName);

        $this->storage->remove($adapterIdentifier, $adapterName);
    }
}
