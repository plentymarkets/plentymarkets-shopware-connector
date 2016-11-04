<?php

namespace PlentyConnector\Connector\TransferObject;

/**
 * Interface MappedTransferObjectInterface
 */
interface MappedTransferObjectInterface extends TransferObjectInterface
{
    /**
     * @return string
     */
    public function getIdentifier();

    /**
     * @return string
     */
    public function getName();
}
