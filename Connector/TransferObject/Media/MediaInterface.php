<?php

namespace PlentyConnector\Connector\TransferObject\Media;

use PlentyConnector\Connector\TransferObject\AttributeableInterface;
use PlentyConnector\Connector\TransferObject\TransferObjectInterface;
use PlentyConnector\Connector\TransferObject\TranslateableInterface;

/**
 * Interface
 */
interface MediaInterface extends TransferObjectInterface, AttributeableInterface, TranslateableInterface
{
    /**
     * @return string
     */
    public function getMediaCategoryIdentifier();

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
