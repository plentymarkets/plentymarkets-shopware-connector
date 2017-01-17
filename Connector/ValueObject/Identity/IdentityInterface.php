<?php

namespace PlentyConnector\Connector\ValueObject\Identity;

use PlentyConnector\Connector\ValueObject\ValueObjectInterface;

/**
 * Interface IdentityInterface.
 */
interface IdentityInterface extends ValueObjectInterface
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
