<?php

namespace PlentyConnector\Connector\IdentityService\Storage;

use PlentyConnector\Connector\ValueObject\Identity\Identity;

/**
 * Interface IdentityStorageInterface.
 */
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
     *
     * @return bool
     */
    public function persist(Identity $identity);

    /**
     * @param Identity $identity
     *
     * @return bool
     */
    public function remove(Identity $identity);
}
