<?php

namespace PlentyConnector\Connector\TransferObject\Media;

use PlentyConnector\Connector\TransferObject\AttributeableInterface;
use PlentyConnector\Connector\TransferObject\SynchronizedTransferObjectInterface;
use PlentyConnector\Connector\TransferObject\TranslateableInterface;

/**
 * Interface
 */
interface MediaInterface extends SynchronizedTransferObjectInterface, AttributeableInterface, TranslateableInterface
{
    /**
     * @return string
     */
    public function getLink();

    /**
     * @return string
     */
    public function getHash();

    /**
     * {@inheritdoc}
     */
    public function getName();

    /**
     * {@inheritdoc}
     */
    public function getAlternateName();
}
