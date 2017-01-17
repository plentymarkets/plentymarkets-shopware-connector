<?php

namespace PlentyConnector\Connector\ValueObject\Definition;

use PlentyConnector\Connector\ValueObject\ValueObjectInterface;

/**
 * Interface DefinitionInterface.
 */
interface DefinitionInterface extends ValueObjectInterface
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

    /**
     * @return string
     */
    public function __toString();
}
