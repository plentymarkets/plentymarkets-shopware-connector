<?php

namespace PlentyConnector\Adapter;

/**
 * Interface AdapterInterface.
 */
interface AdapterInterface
{
    /**
     * returns the unique name of the adapter.
     *
     * @return string
     */
    public static function getName();
}
