<?php

namespace PlentyConnector\Adapter;

interface AdapterInterface
{
    /**
     * returns the unique name of the adapter.
     *
     * @return string
     */
    public function getName(): string;
}
