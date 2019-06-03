<?php

namespace PlentymarketsAdapter;

use PlentyConnector\Adapter\AdapterInterface;

class PlentymarketsAdapter implements AdapterInterface
{
    const NAME = 'PlentymarketsAdapter';

    /**
     * @return string
     */
    public function getName(): string
    {
        return self::NAME;
    }
}
