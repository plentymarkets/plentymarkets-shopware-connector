<?php

namespace SystemConnector\IdentityService;

use SystemConnector\IdentityService\Struct\Identity;

interface IdentityServiceInterface
{
    /**
     * @return null|Identity
     */
    public function findOneBy(array $criteria = []);

    /**
     * @return null|Identity[]
     */
    public function findBy(array $criteria = []);

    /**
     * @param string $objectIdentifier
     * @param string $objectType
     * @param string $adapterIdentifier
     * @param string $adapterName
     */
    public function insert($objectIdentifier, $objectType, $adapterIdentifier, $adapterName): Identity;

    /**
     * @param string $adapterIdentifier
     * @param string $adapterName
     * @param string $objectType
     */
    public function findOneOrCreate($adapterIdentifier, $adapterName, $objectType): Identity;

    /**
     * @param string $adapterIdentifier
     * @param string $adapterName
     * @param string $objectType
     */
    public function findOneOrThrow($adapterIdentifier, $adapterName, $objectType): Identity;

    public function remove(Identity $identity);

    public function update(Identity $identity, array $params = []);

    public function exists(array $criteria = []): bool;

    /**
     * @param string $objectIdentifier
     * @param string $objectType
     * @param string $adapterName
     */
    public function isMappedIdentity($objectIdentifier, $objectType, $adapterName): bool;
}
