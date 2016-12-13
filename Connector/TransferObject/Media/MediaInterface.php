<?php

namespace PlentyConnector\Connector\TransferObject\Media;

use PlentyConnector\Connector\TransferObject\TransferObjectInterface;
use PlentyConnector\Connector\TransferObject\TranslateableInterface;

/**
 * Interface
 */
interface MediaInterface extends TransferObjectInterface, TranslateableInterface
{
    /**
     * @return string
     */
    public function getIdentifier();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getLink();

    /**
     * @return string
     */
    public function getHash();
}
