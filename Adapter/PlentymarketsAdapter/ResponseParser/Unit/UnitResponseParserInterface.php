<?php

namespace PlentymarketsAdapter\ResponseParser\Unit;

use PlentyConnector\Connector\TransferObject\Unit\UnitInterface;

/**
 * Interface UnitResponseParserInterface.
 */
interface UnitResponseParserInterface
{
    /**
     * @param array $entry
     *
     * @return null|UnitInterface
     */
    public function parse(array $entry);
}
