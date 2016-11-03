<?php

namespace PlentymarketsAdapter;

use PlentyConnector\Adapter\AdapterInterface;

/**
 * Class  PlentymarketsAdapter.
 */
class PlentymarketsAdapter implements AdapterInterface
{
    const NAME = 'PlentymarketsAdapter';

    /**
     * {@inheritdoc}
     */
    public static function getName()
    {
        return self::NAME;
    }
}
