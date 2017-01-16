<?php

namespace PlentyConnector\Connector\QueryBus\Query;

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
