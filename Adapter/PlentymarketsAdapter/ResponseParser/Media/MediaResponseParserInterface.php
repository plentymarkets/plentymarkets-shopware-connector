<?php

namespace PlentymarketsAdapter\ResponseParser\Media;

use PlentyConnector\Connector\TransferObject\Media\Media;

interface MediaResponseParserInterface
{
    /**
     * @param array $entry
     *
     * @return Media
     */
    public function parse(array $entry);
}
