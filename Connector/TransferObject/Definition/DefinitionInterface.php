<?php

namespace PlentyConnector\Connector\TransferObject\Definition;

use PlentyConnector\Connector\TransferObject\TransferObjectInterface;

/**
 * Interface DefinitionInterface.
 */
interface DefinitionInterface extends TransferObjectInterface
{
    /**
     * @return string
     */
    public function getOriginAdapterName();

    /**
     * @return string
     */
    public function getDestinationAdapterName();

    /**
     * @return string
     */
    public function getObjectType();
}
