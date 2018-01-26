<?php

namespace ShopwareAdapter\ResponseParser\Unit;

use PlentyConnector\Connector\TransferObject\Unit\Unit;

/**
 * Interface UnitResponseParserInterface
 */
interface UnitResponseParserInterface
{
    /**
     * @param array $entry
     *
     * @return null|Unit
     */
    public function parse(array $entry);
}
