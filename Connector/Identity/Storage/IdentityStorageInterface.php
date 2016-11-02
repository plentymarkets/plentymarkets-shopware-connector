<?php

namespace PlentyConnector\Connector\Identity\Storage;

use PlentyConnector\Connector\TransferObject\Identity\IdentityInterface;

/**
 * Interface IdentityStorageInterface
 *
 * @package PlentyConnector\Connector\Identity\Storage
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
     */
    public function remove($adapterIdentifier, $adapterName);
}
