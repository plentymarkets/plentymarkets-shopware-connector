<?php

namespace ShopwareAdapter;

use PlentyConnector\Adapter\AdapterInterface;

/**
 * Class ShopwareAdapter.
 */
class ShopwareAdapter implements AdapterInterface
{
    const NAME = 'ShopwareAdapter';

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
