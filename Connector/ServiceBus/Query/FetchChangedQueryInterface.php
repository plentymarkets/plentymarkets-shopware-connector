<?php

namespace PlentyConnector\Connector\ServiceBus\Query;

/**
 * Interface FetchChangedQueryInterface
 */
interface FetchChangedQueryInterface extends QueryInterface
{
    /**
     * @return string
     */
    public function getAdapterName();
}
