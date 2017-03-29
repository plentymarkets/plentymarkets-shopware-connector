<?php

namespace PlentymarketsAdapter\ResponseParser;

use PlentyConnector\Connector\TransferObject\TransferObjectInterface;

/**
 * Interface ResponseParserInterface.
 */
interface ResponseParserInterface
{
    /**
     * @param array $entry
     *
     * @return null|TransferObjectInterface[]
     */
    public function parse(array $entry);
}
