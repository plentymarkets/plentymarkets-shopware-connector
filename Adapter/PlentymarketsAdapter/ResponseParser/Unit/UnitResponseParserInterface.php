<?php

namespace PlentymarketsAdapter\ResponseParser\Unit;

use PlentyConnector\Connector\TransferObject\Unit\Unit;

interface UnitResponseParserInterface
{
    /**
     * @param array $entry
     *
     * @return null|Unit
     */
    public function parse(array $entry);
}
