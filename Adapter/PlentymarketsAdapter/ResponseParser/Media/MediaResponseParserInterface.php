<?php

namespace PlentymarketsAdapter\ResponseParser\Media;

use SystemConnector\TransferObject\Media\Media;

interface MediaResponseParserInterface
{
    public function parse(array $entry): Media;
}
