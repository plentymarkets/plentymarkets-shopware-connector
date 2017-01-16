<?php

namespace PlentyConnector\Connector\IdentityService;

use PlentyConnector\Connector\TransferObject\Identity\IdentityInterface;

/**
 * Interface IdentityServiceInterface.
 */
interface IdentityServiceInterface
{
    /**
     * @param array $criteria
     *
     * @return IdentityInterface|null
     */
    public function findOneBy(array $criteria = []);

    /**
     * @param array $criteria
     *
     * @return IdentityInterface[]|null
     */
    public function findby(array $criteria = []);

    /**
     * @param string $objectIdentifier
     * @param string $objectType
     * @param string $adapterIdentifier
     * @param string $adapterName
     *
     * @return IdentityInterface
     */
    public function create($objectIdentifier, $objectType, $adapterIdentifier, $adapterName);

    /**
     * @param string $adapterIdentifier
     * @param string $adapterName
     * @param string $objectType
     *
     * @return IdentityInterface
     */
    public function findOneOrCreate($adapterIdentifier, $adapterName, $objectType);

    /**
     * @param IdentityInterface $identity
     */
    public function remove(IdentityInterface $identity);

    /**
     * @param array $criteria
     *
     * @return bool
     */
    public function exists(array $criteria = []);
}
