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
     * @return IdentityInterface
     */
    public function findBy(array $criteria = []);

    /**
     * @param IdentityInterface $identity
     */
    public function persist(IdentityInterface $identity);

    /**
     * @param $adapterIdentifier
     * @param $adapterName
     * @param $objectType
     */
    public function remove($adapterIdentifier, $adapterName, $objectType);
}
