<?php

namespace ShopwareAdapter\ResponseParser;

use PlentyConnector\Connector\TransferObject\TransferObjectInterface;

/**
 * Interface ResponseParserInterface
 */
interface ResponseParserInterface
{
    /**
     * @param array $entry
     *
     * @return TransferObjectInterface|null
     */
    public function parse(array $entry);
}
