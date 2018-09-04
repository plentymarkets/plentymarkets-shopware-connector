<?php

namespace PlentymarketsAdapter;

use PlentyConnector\Adapter\AdapterInterface;

class PlentymarketsAdapter implements AdapterInterface
{
    const NAME = 'PlentymarketsAdapter';

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
