<?php

namespace PlentyConnector\Adapter;

interface AdapterInterface
{
    /**
     * returns the unique name of the adapter.
     */
    public function getName(): string;
}
