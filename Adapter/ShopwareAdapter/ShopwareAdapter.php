<?php

namespace ShopwareAdapter;

use PlentyConnector\Adapter\AdapterInterface;

class ShopwareAdapter implements AdapterInterface
{
    const NAME = 'ShopwareAdapter';

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return self::NAME;
    }
}
