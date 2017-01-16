<?php

namespace PlentyConnector\Connector\IdentityService;

use Assert\Assertion;
use PlentyConnector\Connector\EventBus\Event\Identity\IdentityCreatedEvent;
use PlentyConnector\Connector\EventBus\Event\Identity\IdentityRemovedEvent;
use PlentyConnector\Connector\IdentityService\Storage\IdentityStorageInterface;
use PlentyConnector\Connector\ServiceBus\ServiceBusInterface;
use PlentyConnector\Connector\TransferObject\Identity\Identity;
use PlentyConnector\Connector\TransferObject\Identity\IdentityInterface;
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
     * @var ServiceBusInterface
     */
    private $eventBus;

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
    public function findOneOrCreate($adapterIdentifier, $adapterName, $objectType)
    {
        Assertion::string($adapterIdentifier);
        Assertion::string($adapterName);
        Assertion::string($objectType);

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
                (string)$adapterIdentifier,
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
            //$this->eventBus->handle(new IdentityCreatedEvent($identity));
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
    public function remove(IdentityInterface $identity)
    {
        $result = $this->storage->remove($identity);

        if ($result) {
            //$this->eventBus->handle(new IdentityRemovedEvent($identity));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function exists(array $criteria = [])
    {
        $identity = $this->findOneBy($criteria);

        return (bool)$identity;
    }
}
