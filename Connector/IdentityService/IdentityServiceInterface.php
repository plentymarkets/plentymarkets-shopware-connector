<?php

namespace SystemConnector\IdentityService;

use SystemConnector\IdentityService\Struct\Identity;

interface IdentityServiceInterface
{
    /**
     * @param array $criteria
     *
     * @return null|Identity
     */
    public function findOneBy(array $criteria = []);

    /**
     * @param array $criteria
     *
     * @return null|Identity[]
     */
    public function findBy(array $criteria = []);

    /**
     * @param string $objectIdentifier
     * @param string $objectType
     * @param string $adapterIdentifier
     * @param string $adapterName
     *
     * @return Identity
     */
    public function insert($objectIdentifier, $objectType, $adapterIdentifier, $adapterName);

    /**
     * @param string $adapterIdentifier
     * @param string $adapterName
     * @param string $objectType
     *
     * @return Identity
     */
    public function findOneOrCreate($adapterIdentifier, $adapterName, $objectType);

    /**
     * @param string $adapterIdentifier
     * @param string $adapterName
     * @param string $objectType
     *
     * @return Identity
     */
    public function findOneOrThrow($adapterIdentifier, $adapterName, $objectType);

    /**
     * @param Identity $identity
     */
    public function remove(Identity $identity);

    /**
     * @param Identity $identity
     * @param array    $params
     */
    public function update(Identity $identity, array $params = []);

    /**
     * @param array $criteria
     *
     * @return bool
     */
    public function exists(array $criteria = []);

    /**
     * @param string $objectIdentifier
     * @param string $objectType
     * @param string $adapterName
     *
     * @return bool
     */
    public function isMappedIdentity($objectIdentifier, $objectType, $adapterName);
}
