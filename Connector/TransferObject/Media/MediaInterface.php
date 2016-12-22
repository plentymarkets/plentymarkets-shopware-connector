<?php

namespace PlentyConnector\Connector\TransferObject\Media;

use PlentyConnector\Connector\TransferObject\TransferObjectInterface;

/**
 * Interface
 */
interface MediaInterface extends TransferObjectInterface
{
    /**
     * @return string
     */
    public function getIdentifier();

    /**
     * @return string
     */
    public function getLink();

    /**
     * @return string
     */
    public function getHash();
}
