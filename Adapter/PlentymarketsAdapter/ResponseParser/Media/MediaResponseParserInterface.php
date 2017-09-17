<?php

namespace PlentymarketsAdapter\ResponseParser\Media;

use Exception;
use PlentyConnector\Connector\TransferObject\Media\Media;

/**
 * Interface MediaResponseParserInterface
 */
interface MediaResponseParserInterface
{
    /**
     * @param array $entry
     *
     * @throws Exception
     *
     * @return Media
     */
    public function parse(array $entry);
}
