<?php

namespace PlentyConnector\Connector\TransferObject\MediaCategory;

use PlentyConnector\Connector\TransferObject\TransferObjectInterface;

/**
 * Interface
 */
interface MediaCategoryInterface extends TransferObjectInterface
{
    /**
     * {@inheritdoc}
     */
    public function getName();
}
