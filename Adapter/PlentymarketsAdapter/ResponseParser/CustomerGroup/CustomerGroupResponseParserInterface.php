<?php

namespace PlentymarketsAdapter\ResponseParser\CustomerGroup;

use SystemConnector\TransferObject\CustomerGroup\CustomerGroup;

interface CustomerGroupResponseParserInterface
{
    /**
     * @return null|CustomerGroup
     */
    public function parse(array $entry);
}
