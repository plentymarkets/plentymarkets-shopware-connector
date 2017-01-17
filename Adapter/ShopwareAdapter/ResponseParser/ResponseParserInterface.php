<?php

namespace ShopwareAdapter\ResponseParser;

use PlentyConnector\Connector\TransferObject\SynchronizedTransferObjectInterface;

/**
 * Interface ResponseParserInterface
 */
interface ResponseParserInterface
{
    /**
     * @param array $entry
     *
     * @return SynchronizedTransferObjectInterface|null
     */
    public function parse(array $entry);
}
