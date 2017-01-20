<?php

namespace PlentyConnector\Connector\ServiceBus\Query;

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
