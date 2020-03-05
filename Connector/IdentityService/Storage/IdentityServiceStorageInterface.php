<?php

namespace SystemConnector\IdentityService\Storage;

use SystemConnector\IdentityService\Struct\Identity;

interface IdentityServiceStorageInterface
{
    /**
     * @return null|Identity[]
     */
    public function findBy(array $criteria = []);

    /**
     * @return null|Identity
     */
    public function findOneBy(array $criteria = []);

    public function insert(Identity $identity);

    /**
     * @return mixed
     */
    public function update(Identity $identity, array $data = []);

    public function remove(Identity $identity);
}
