<?php

namespace PlentyConnector\Connector\TransferObject\Mapping;

use PlentyConnector\Connector\TransferObject\MappedTransferObjectInterface;
use PlentyConnector\Connector\TransferObject\TransferObjectInterface;

/**
 * Interface MappingInterface
 */
interface MappingInterface extends TransferObjectInterface
{
    /**
     * @return string
     */
    public function getOriginAdapterName();

    /**
     * @return MappedTransferObjectInterface[]
     */
    public function getOriginTransferObjects();

    /**
     * @return string
     */
    public function getDestinationAdapterName();

    /**
     * @return MappedTransferObjectInterface[]
     */
    public function getDestinationTransferObjects();

    /**
     * @return string
     */
    public function getObjectType();
}
