<?php

namespace ShopwareAdapter\ResponseParser\Unit;

use SystemConnector\TransferObject\Unit\Unit;

interface UnitResponseParserInterface
{
    /**
     * @return null|Unit
     */
    public function parse(array $entry);
}
