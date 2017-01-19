<?php

namespace PlentyConnector\Connector\TransferObject\MediaCategory;

use PlentyConnector\Connector\TransferObject\SynchronizedTransferObjectInterface;

/**
 * Interface
 */
interface MediaCategoryInterface extends SynchronizedTransferObjectInterface
{
    /**
     * {@inheritdoc}
     */
    public function getName();
}
