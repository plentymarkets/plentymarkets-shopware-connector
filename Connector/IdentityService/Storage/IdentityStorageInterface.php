<?php

namespace PlentyConnector\Connector\IdentityService\Storage;

use PlentyConnector\Connector\TransferObject\Identity\IdentityInterface;

/**
 * Interface IdentityStorageInterface.
 */
interface IdentityStorageInterface
{
    /**
     * @param array $criteria
     *
     * @return IdentityInterface|null
     */
    public function findBy(array $criteria = []);

    /**
     * @param array $criteria
     *
     * @return IdentityInterface[]|null
     */
    public function findOneBy(array $criteria = []);

    /**
     * @param IdentityInterface $identity
     *
     * @return bool
     */
    public function persist(IdentityInterface $identity);

    /**
     * @param IdentityInterface $identity
     *
     * @return bool
     */
    public function remove(IdentityInterface $identity);
}
