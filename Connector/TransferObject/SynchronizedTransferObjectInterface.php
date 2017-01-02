<?php

namespace PlentyConnector\Connector\TransferObject;

/**
 * Interface SynchronizedTransferObjectInterface
 */
interface SynchronizedTransferObjectInterface extends TransferObjectInterface
{
    /**
     * @return string
     */
    public function getIdentifier();
}
