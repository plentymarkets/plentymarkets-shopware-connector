<?php

namespace ShopwareAdapter\ResponseParser;

use PlentyConnector\Connector\TransferObject\Order\OrderInterface;

/**
 * Interface ResponseParserInterface
 */
interface ResponseParserInterface
{
    /**
     * @param $entry
     *
     * @return OrderInterface
     */
    public function parseOrder($entry);
}
