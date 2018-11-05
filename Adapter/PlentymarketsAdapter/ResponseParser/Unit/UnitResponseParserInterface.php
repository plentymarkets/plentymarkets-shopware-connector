<?php

namespace PlentymarketsAdapter\ResponseParser\Unit;

use SystemConnector\TransferObject\Unit\Unit;

interface UnitResponseParserInterface
{
    /**
     * @param array $entry
     *
     * @return null|Unit
     */
    public function parse(array $entry);
}
