<?php

namespace PlentymarketsAdapter\ResponseParser\Media;

use PlentyConnector\Connector\TransferObject\Media\MediaInterface;

/**
 * Interface MediaResponseParserInterface
 */
interface MediaResponseParserInterface
{
    /**
     * @param array $entry
     *
     * @return MediaInterface|null
     */
    public function parse(array $entry);
}
