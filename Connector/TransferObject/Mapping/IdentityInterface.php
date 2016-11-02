<?php

namespace PlentyConnector\Connector\TransferObject\Identity;

use PlentyConnector\Connector\TransferObject\TransferObjectInterface;

/**
 * Interface IdentityInterface
 *
 * @package PlentyConnector\Connector\TransferObject\Identity
 */
interface IdentityInterface extends TransferObjectInterface
{
    /**
     * @return string
     */
    public function getObjectIdentifier();

    /**
     * @return string
     */
    public function getObjectType();

    /**
     * @return string
     */
    public function getAdapterIdentifier();

    /**
     * @return string
     */
    public function getAdapterName();
}
