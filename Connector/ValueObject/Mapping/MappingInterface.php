<?php

namespace PlentyConnector\Connector\ValueObject\Mapping;

use PlentyConnector\Connector\TransferObject\TransferObjectInterface;
use PlentyConnector\Connector\ValueObject\ValueObjectInterface;

/**
 * Interface MappingInterface
 */
interface MappingInterface extends ValueObjectInterface
{
    /**
     * @return string
     */
    public function getOriginAdapterName();

    /**
     * @return TransferObjectInterface[]
     */
    public function getOriginTransferObjects();

    /**
     * @return string
     */
    public function getDestinationAdapterName();

    /**
     * @return TransferObjectInterface[]
     */
    public function getDestinationTransferObjects();

    /**
     * @return string
     */
    public function getObjectType();
}
