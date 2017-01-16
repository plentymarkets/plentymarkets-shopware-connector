<?php

namespace PlentyConnector\Connector\QueryBus\Query;

/**
 * Interface FetchAllQueryInterface
 */
interface FetchAllQueryInterface extends QueryInterface
{
    /**
     * @return string
     */
    public function getAdapterName();
}
