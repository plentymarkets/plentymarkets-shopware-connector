<?php

namespace ShopwareAdapter\ResponseParser\CustomerGroup;

use PlentyConnector\Connector\TransferObject\CustomerGroup\CustomerGroupInterface;

/**
 * Interface CustomerGroupResponseParserInterface
 */
interface CustomerGroupResponseParserInterface
{
    /**
     * @param array $entry
     *
     * @return CustomerGroupInterface|null
     */
    public function parse(array $entry);
}
