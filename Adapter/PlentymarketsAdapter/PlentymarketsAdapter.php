<?php

namespace PlentymarketsAdapter;

use PlentyConnector\Adapter\AdapterInterface;

class PlentymarketsAdapter implements AdapterInterface
{
    const NAME = 'PlentymarketsAdapter';

    public function getName(): string
    {
        return self::NAME;
    }
}
