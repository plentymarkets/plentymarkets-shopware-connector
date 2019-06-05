<?php

namespace SystemConnector\ServiceBus\Query;

interface QueryInterface
{
    /**
     * @return array
     */
    public function toArray(): array;
}
