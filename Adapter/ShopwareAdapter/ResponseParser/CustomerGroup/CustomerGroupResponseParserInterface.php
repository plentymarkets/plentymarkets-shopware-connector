<?php

namespace ShopwareAdapter\ResponseParser\CustomerGroup;

use SystemConnector\TransferObject\CustomerGroup\CustomerGroup;

interface CustomerGroupResponseParserInterface
{
    /**
     * @param array $entry
     *
     * @return null|CustomerGroup
     */
    public function parse(array $entry);
}
