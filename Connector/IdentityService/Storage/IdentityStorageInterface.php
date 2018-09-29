<?php

namespace PlentyConnector\Connector\IdentityService\Storage;

use PlentyConnector\Connector\ValueObject\Identity\Identity;

interface IdentityStorageInterface
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
    public function persist(Identity $identity);

    /**
     * @param Identity $identity
     * @param array $params
     *
     * @return null|Identity
     */
    public function update(Identity $identity, array $params = []);

    /**
     * @param Identity $identity
     */
    public function remove(Identity $identity);
}
