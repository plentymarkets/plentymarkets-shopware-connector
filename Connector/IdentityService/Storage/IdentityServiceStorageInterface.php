<?php

namespace SystemConnector\IdentityService\Storage;

use SystemConnector\ValueObject\Identity\Identity;

interface IdentityServiceStorageInterface
{
    /**
     * @param array $criteria
     *
     * @return null|Identity[]
     */
    public function findBy(array $criteria = []);

    /**
     * @param array $criteria
     *
     * @return null|Identity
     */
    public function findOneBy(array $criteria = []);

    /**
     * @param Identity $identity
     */
    public function insert(Identity $identity);

    /**
     * @param Identity $identity
     * @param array    $params
     *
     * @return null|Identity
     */
    public function update(Identity $identity, array $params = []);

    /**
     * @param Identity $identity
     */
    public function remove(Identity $identity);
}
